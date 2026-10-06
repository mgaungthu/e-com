<?php

namespace App\Services\Auth;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class AppleAuthService
{
    private const KEYS_URL = 'https://appleid.apple.com/auth/keys';

    private const CACHE_KEY = 'auth.apple.signing_keys';

    public function verifyIdToken(#[\SensitiveParameter] string $identityToken): array
    {
        $clientId = config('services.apple.client_id');

        if (! is_string($clientId) || trim($clientId) === '') {
            throw new RuntimeException('Apple authentication is not configured.');
        }

        try {
            $parts = explode('.', $identityToken);

            if (count($parts) !== 3 || strlen($identityToken) > 16384) {
                throw new RuntimeException('Invalid token.');
            }

            // The unverified header is used only to select an Apple signing key.
            $header = JWT::jsonDecode(JWT::urlsafeB64Decode($parts[0]));
            $keyId = $header->kid ?? null;

            if (($header->alg ?? null) !== 'RS256' || ! is_string($keyId) || $keyId === '') {
                throw new RuntimeException('Invalid signing key.');
            }

            $jwks = Cache::remember(self::CACHE_KEY, 3600, fn () => $this->fetchKeys());
            $keys = JWK::parseKeySet($jwks);

            // Refresh once for key rotation; random unknown kids cannot force unlimited fetches.
            if (! isset($keys[$keyId]) && Cache::add(self::CACHE_KEY.'.refresh', true, 60)) {
                $jwks = $this->fetchKeys();
                Cache::put(self::CACHE_KEY, $jwks, 3600);
                $keys = JWK::parseKeySet($jwks);
            }

            $payload = (array) JWT::decode($identityToken, $keys);
            $audiences = $payload['aud'] ?? [];
            $audiences = is_array($audiences) ? $audiences : [$audiences];
            $subject = $payload['sub'] ?? null;

            if (
                ($payload['iss'] ?? null) !== 'https://appleid.apple.com'
                || ! in_array($clientId, $audiences, true)
                || ! is_int($payload['exp'] ?? null)
                || $payload['exp'] <= time()
                || ! is_string($subject)
                || trim($subject) === ''
                || strlen($subject) > 255
            ) {
                throw new RuntimeException('Invalid identity claims.');
            }

            $email = $payload['email'] ?? null;

            if ($email !== null) {
                if (! is_string($email)) {
                    throw new RuntimeException('Invalid email claim.');
                }

                $email = strtolower(trim($email));

                if (strlen($email) > 255 || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new RuntimeException('Invalid email claim.');
                }
            }

            return [
                'sub' => $subject,
                'email' => $email,
                'email_verified' => in_array($payload['email_verified'] ?? null, [true, 'true'], true),
                'is_private_email' => in_array($payload['is_private_email'] ?? null, [true, 'true'], true),
            ];
        } catch (Throwable) {
            // Do not attach JWT exceptions: some contain decoded personal claims.
            throw new RuntimeException('Unable to verify Apple identity token.');
        }
    }

    private function fetchKeys(): array
    {
        $jwks = Http::acceptJson()->connectTimeout(3)->timeout(5)
            ->get(self::KEYS_URL)->throw()->json();

        if (! is_array($jwks) || ! is_array($jwks['keys'] ?? null)) {
            throw new RuntimeException('Invalid Apple signing keys.');
        }

        $jwks['keys'] = array_values(array_filter($jwks['keys'], fn ($key) => is_array($key)
            && ($key['kty'] ?? null) === 'RSA'
            && ($key['alg'] ?? null) === 'RS256'
            && ($key['use'] ?? null) === 'sig'));

        // Validate before caching a response from the key endpoint.
        JWK::parseKeySet($jwks);

        return $jwks;
    }
}
