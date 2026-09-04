<?php

namespace Tests\Feature\Api\V1;

use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\VerifyEmailCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_fifth_incorrect_code_locks_verification_without_deleting_the_code(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('Test Device')->plainTextToken;
        $verification = $this->verificationFor($user, '1234');

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $this->withToken($token)
                ->postJson('/api/v1/auth/email/verify', ['code' => '0000'])
                ->assertUnprocessable()
                ->assertJsonPath('message', 'Verification code is incorrect.');
        }

        $this->withToken($token)
            ->postJson('/api/v1/auth/email/verify', ['code' => '0000'])
            ->assertTooManyRequests()
            ->assertJsonPath('message', 'Too many incorrect attempts. Please request a new verification code.');

        $this->assertDatabaseHas('email_verification_codes', [
            'id' => $verification->id,
            'attempts' => 5,
        ]);
    }

    public function test_resend_resets_attempts_and_refreshes_the_active_code(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $token = $user->createToken('Test Device')->plainTextToken;
        $verification = $this->verificationFor($user, '1234', attempts: 5, lastSentAt: now()->subMinute());

        $this->withToken($token)
            ->postJson('/api/v1/auth/email/resend')
            ->assertOk()
            ->assertJsonPath('data.requires_email_verification', true);

        $verification->refresh();

        $this->assertSame(0, $verification->attempts);
        $this->assertTrue($verification->expires_at->isAfter(now()->addMinutes(9)));
        $this->assertTrue($verification->last_sent_at->isAfter(now()->subMinute()));
        Notification::assertSentTo($user, VerifyEmailCodeNotification::class);
    }

    public function test_email_update_keeps_the_session_and_issues_a_new_verification_code(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'old@example.com']);
        $token = $user->createToken('Test Device')->plainTextToken;

        $this->withToken($token)
            ->patchJson('/api/v1/auth/email', ['email' => 'new@example.com'])
            ->assertOk()
            ->assertJsonPath('data.user.email', 'new@example.com')
            ->assertJsonPath('data.user.email_verified_at', null)
            ->assertJsonPath('data.requires_email_verification', true);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
        $this->assertDatabaseHas('email_verification_codes', [
            'user_id' => $user->id,
            'attempts' => 0,
        ]);
        Notification::assertSentTo($user, VerifyEmailCodeNotification::class);
    }

    public function test_correct_code_verifies_the_user_and_removes_the_verification_record(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('Test Device')->plainTextToken;
        $verification = $this->verificationFor($user, '1234');

        $this->withToken($token)
            ->postJson('/api/v1/auth/email/verify', ['code' => '1234'])
            ->assertOk()
            ->assertJsonPath('data.requires_email_verification', false)
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing('email_verification_codes', [
            'id' => $verification->id,
        ]);
    }

    public function test_unverified_user_can_use_auth_verification_routes_but_not_customer_routes(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('Test Device')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);

        $this->withToken($token)
            ->getJson('/api/v1/cart')
            ->assertForbidden()
            ->assertExactJson([
                'success' => false,
                'message' => 'Your email address is not verified.',
            ]);
    }

    public function test_verified_user_reaches_customer_route_controllers(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test Device')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->withToken($token)
            ->getJson('/api/v1/addresses')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    private function verificationFor(
        User $user,
        string $code,
        int $attempts = 0,
        mixed $lastSentAt = null,
    ): EmailVerificationCode {
        return EmailVerificationCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'attempts' => $attempts,
            'expires_at' => now()->addMinutes(10),
            'last_sent_at' => $lastSentAt ?? now()->subMinutes(2),
        ]);
    }
}
