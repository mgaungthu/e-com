<?php

namespace App\Services\Auth;

use Google\Client;
use RuntimeException;

class GoogleAuthService
{
    public function verifyIdToken(string $idToken): array
    {
        $clientId = config('services.google.client_id');

        if (! is_string($clientId) || trim($clientId) === '') {
            throw new RuntimeException(
                'Google authentication is not configured.'
            );
        }

        $client = new Client([
            'client_id' => $clientId,
        ]);

        $payload = $client->verifyIdToken($idToken);

        if (! is_array($payload)) {
            throw new RuntimeException(
                'Invalid Google ID token.'
            );
        }

        $googleId = $payload['sub'] ?? null;
        $email = $payload['email'] ?? null;
        $emailVerified = $payload['email_verified'] ?? false;

        if (! is_string($googleId) || $googleId === '') {
            throw new RuntimeException(
                'Google account identifier is missing.'
            );
        }

        if (! is_string($email) || $email === '') {
            throw new RuntimeException(
                'Google account email is missing.'
            );
        }

        if (! $emailVerified) {
            throw new RuntimeException(
                'Google account email is not verified.'
            );
        }

        return $payload;
    }
}