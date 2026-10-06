<?php

namespace Tests\Feature;

use App\Services\Auth\AppleAuthService;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class AppleAuthServiceTest extends TestCase
{
    private static string $privateKey = '';

    private static array $jwk;

    private array $keyResponse;

    private int $keyStatus = 200;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, self::$privateKey);
        $details = openssl_pkey_get_details($key);
        self::$jwk = [
            'kid' => 'test-apple-key', 'kty' => 'RSA', 'use' => 'sig', 'alg' => 'RS256',
            'n' => JWT::urlsafeB64Encode($details['rsa']['n']),
            'e' => JWT::urlsafeB64Encode($details['rsa']['e']),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.apple.client_id' => 'com.burmeseshave.club']);
        Cache::flush();
        Http::preventStrayRequests();
        $this->keyResponse = ['keys' => [self::$jwk]];
        Http::fake(['https://appleid.apple.com/auth/keys' => fn () => Http::response($this->keyResponse, $this->keyStatus)]);
    }

    private function token(array $overrides = [], string $kid = 'test-apple-key'): string
    {
        $payload = array_replace([
            'iss' => 'https://appleid.apple.com',
            'aud' => 'com.burmeseshave.club',
            'sub' => 'apple-subject',
            'iat' => time() - 10,
            'exp' => time() + 600,
            'email' => ' Customer@PrivateRelay.AppleID.com ',
            'email_verified' => 'true',
            'is_private_email' => 'true',
        ], $overrides);
        // Sign even deliberately invalid claims; JWT::encode validates some claim types itself.
        $header = JWT::urlsafeB64Encode(json_encode(['typ' => 'JWT', 'alg' => 'RS256', 'kid' => $kid]));
        $body = JWT::urlsafeB64Encode(json_encode($payload));
        openssl_sign($header.'.'.$body, $signature, self::$privateKey, OPENSSL_ALGO_SHA256);

        return $header.'.'.$body.'.'.JWT::urlsafeB64Encode($signature);
    }

    public function test_valid_signature_and_claims_are_verified_and_keys_are_cached(): void
    {
        $service = app(AppleAuthService::class);
        $expected = [
            'sub' => 'apple-subject',
            'email' => 'customer@privaterelay.appleid.com',
            'email_verified' => true,
            'is_private_email' => true,
        ];

        $this->assertSame($expected, $service->verifyIdToken($this->token()));
        $this->assertSame($expected, $service->verifyIdToken($this->token()));
        Http::assertSentCount(1);
    }

    public static function invalidClaims(): array
    {
        return [
            'wrong issuer' => [['iss' => 'https://attacker.example.com']],
            'missing issuer' => [['iss' => null]],
            'wrong audience' => [['aud' => 'another.client']],
            'wrong audience list' => [['aud' => ['another.client']]],
            'missing audience' => [['aud' => null]],
            'expired' => [['exp' => 1]],
            'missing expiration' => [['exp' => null]],
            'invalid expiration' => [['exp' => 'not-a-timestamp']],
            'empty subject' => [['sub' => '  ']],
            'missing subject' => [['sub' => null]],
            'invalid subject type' => [['sub' => ['apple-subject']]],
            'oversized subject' => [['sub' => str_repeat('x', 256)]],
            'invalid email' => [['email' => 'not-an-email']],
            'invalid email type' => [['email' => ['email@example.com']]],
            'future issued at' => [['iat' => PHP_INT_MAX]],
            'future not before' => [['nbf' => PHP_INT_MAX]],
        ];
    }

    #[DataProvider('invalidClaims')]
    public function test_signed_tokens_with_invalid_claims_are_rejected(array $claims): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to verify Apple identity token.');
        app(AppleAuthService::class)->verifyIdToken($this->token($claims));
    }

    public function test_email_claims_can_be_absent(): void
    {
        $token = JWT::encode([
            'iss' => 'https://appleid.apple.com', 'aud' => 'com.burmeseshave.club',
            'sub' => 'apple-subject', 'exp' => time() + 600,
        ], self::$privateKey, 'RS256', 'test-apple-key');

        $this->assertSame([
            'sub' => 'apple-subject', 'email' => null, 'email_verified' => false, 'is_private_email' => false,
        ], app(AppleAuthService::class)->verifyIdToken($token));
    }

    public function test_required_claims_cannot_be_omitted(): void
    {
        $token = JWT::encode([
            'iss' => 'https://appleid.apple.com', 'aud' => 'com.burmeseshave.club', 'sub' => 'apple-subject',
        ], self::$privateKey, 'RS256', 'test-apple-key');

        $this->expectException(RuntimeException::class);
        app(AppleAuthService::class)->verifyIdToken($token);
    }

    public static function booleanClaims(): array
    {
        return [
            'boolean true' => [true, true], 'string true' => ['true', true],
            'boolean false' => [false, false], 'string false' => ['false', false],
            'missing' => [null, false], 'truthy string' => ['yes', false],
            'number' => [1, false], 'array' => [[true], false],
        ];
    }

    #[DataProvider('booleanClaims')]
    public function test_verified_and_private_email_flags_are_not_coerced_from_truthy_values(mixed $value, bool $expected): void
    {
        $claims = app(AppleAuthService::class)->verifyIdToken($this->token([
            'email_verified' => $value, 'is_private_email' => $value,
        ]));

        $this->assertSame($expected, $claims['email_verified']);
        $this->assertSame($expected, $claims['is_private_email']);
    }

    public function test_audience_list_can_include_configured_client(): void
    {
        $claims = app(AppleAuthService::class)->verifyIdToken($this->token(['aud' => ['com.burmeseshave.club']]));
        $this->assertSame('apple-subject', $claims['sub']);
    }

    public function test_tampered_signed_payload_is_rejected(): void
    {
        $parts = explode('.', $this->token());
        $payload = json_decode(JWT::urlsafeB64Decode($parts[1]), true);
        $payload['sub'] = 'attacker';
        $parts[1] = JWT::urlsafeB64Encode(json_encode($payload));

        $this->expectException(RuntimeException::class);
        app(AppleAuthService::class)->verifyIdToken(implode('.', $parts));
    }

    public function test_wrong_signing_key_is_rejected(): void
    {
        $wrongKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $parts = explode('.', $this->token());
        openssl_sign($parts[0].'.'.$parts[1], $signature, $wrongKey, OPENSSL_ALGO_SHA256);
        $parts[2] = JWT::urlsafeB64Encode($signature);

        $this->expectException(RuntimeException::class);
        app(AppleAuthService::class)->verifyIdToken(implode('.', $parts));
    }

    public function test_wrong_algorithm_is_rejected_without_fetching_keys(): void
    {
        $token = JWT::encode(['sub' => 'attacker'], str_repeat('a', 64), 'HS256', 'test-apple-key');

        try {
            app(AppleAuthService::class)->verifyIdToken($token);
        } catch (RuntimeException) {
            Http::assertNothingSent();

            return;
        }

        $this->fail('An HMAC token must not be accepted.');
    }

    public function test_unknown_key_refreshes_cached_keys_for_rotation(): void
    {
        app(AppleAuthService::class)->verifyIdToken($this->token());
        $rotated = array_replace(self::$jwk, ['kid' => 'rotated-key']);
        $this->keyResponse = ['keys' => [$rotated]];

        $this->assertSame('apple-subject', app(AppleAuthService::class)->verifyIdToken($this->token(kid: 'rotated-key'))['sub']);
        Http::assertSentCount(2);
    }

    public function test_unknown_keys_are_rejected_and_refresh_attempts_are_bounded(): void
    {
        app(AppleAuthService::class)->verifyIdToken($this->token());

        foreach (['unknown-a', 'unknown-b'] as $kid) {
            try {
                app(AppleAuthService::class)->verifyIdToken($this->token(kid: $kid));
            } catch (RuntimeException $exception) {
                $this->assertSame('Unable to verify Apple identity token.', $exception->getMessage());

                continue;
            }

            $this->fail('An unknown key must not be accepted.');
        }

        Http::assertSentCount(2);
    }

    public function test_key_endpoint_failure_is_rejected_and_not_cached(): void
    {
        $this->keyStatus = 503;

        try {
            app(AppleAuthService::class)->verifyIdToken($this->token());
        } catch (RuntimeException) {
            $this->assertNull(Cache::get('auth.apple.signing_keys'));

            return;
        }

        $this->fail('Verification must fail when signing keys are unavailable.');
    }

    public function test_missing_configuration_is_rejected_before_any_network_request(): void
    {
        config(['services.apple.client_id' => null]);

        try {
            app(AppleAuthService::class)->verifyIdToken($this->token());
        } catch (RuntimeException $exception) {
            $this->assertSame('Apple authentication is not configured.', $exception->getMessage());
            Http::assertNothingSent();

            return;
        }

        $this->fail('Verification must require an explicit configured audience.');
    }
}
