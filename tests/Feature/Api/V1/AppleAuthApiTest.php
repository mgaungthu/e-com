<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Services\Auth\AppleAuthService;
use App\Services\Auth\GoogleAuthService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppleAuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('customer');
        Http::preventStrayRequests();
    }

    private function authUrl(string $path): string
    {
        return 'https://'.config('app.api_domain').'/v1/auth/'.$path;
    }

    private function appleClaims(array $overrides = []): void
    {
        $this->mock(AppleAuthService::class, function ($mock) use ($overrides) {
            $mock->shouldReceive('verifyIdToken')->once()->with('apple-token')->andReturn(array_replace([
                'sub' => 'apple-a',
                'email' => 'customer@privaterelay.appleid.com',
                'email_verified' => true,
                'is_private_email' => true,
            ], $overrides));
        });
    }

    public function test_new_apple_customer_receives_the_existing_session_contract(): void
    {
        $this->appleClaims();

        $response = $this->postJson($this->authUrl('apple'), [
            'identity_token' => 'apple-token',
            'device_name' => 'iPhone',
            'email' => 'attacker@example.com',
            'name' => 'Untrusted name',
            'role' => 'admin',
        ])->assertOk()->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.requires_email_verification', false)
            ->assertJsonPath('data.user.has_apple_account', true)
            ->assertJsonPath('data.user.has_google_account', false)
            ->assertJsonPath('data.user.email', 'customer@privaterelay.appleid.com')
            ->assertJsonPath('data.user.name', 'customer@privaterelay.appleid.com')
            ->assertJsonMissingPath('data.user.apple_id')
            ->assertJsonMissingPath('data.user.google_id')
            ->assertJsonMissingPath('data.user.password');

        $this->assertEqualsCanonicalizing(['success', 'message', 'data'], array_keys($response->json()));
        $this->assertEqualsCanonicalizing(['user', 'token', 'token_type', 'requires_email_verification'], array_keys($response->json('data')));
        $user = User::query()->sole();
        $this->assertSame('apple-a', $user->apple_id);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertSame('customer', $user->role);
        $this->assertNotEmpty($user->password);
        $this->assertFalse(Hash::needsRehash($user->password));
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_login_ip);
        $this->assertSame(['customer'], $user->tokens()->sole()->abilities);
        $this->assertSame('iPhone', $user->tokens()->sole()->name);
        $this->withToken($response->json('data.token'))->getJson($this->authUrl('me'))
            ->assertOk()->assertJsonPath('data.user.id', $user->id);
    }

    public static function repeatEmails(): array
    {
        return [
            'same verified email' => ['stored@example.com', true, true],
            'email omitted' => [null, false, false],
            'different verified email' => ['other@example.com', true, false],
            'unverified stored email' => ['stored@example.com', false, false],
        ];
    }

    #[DataProvider('repeatEmails')]
    public function test_repeat_login_uses_apple_subject_and_only_verifies_the_matching_email(?string $email, bool $verified, bool $expectedVerified): void
    {
        $user = User::factory()->unverified()->create([
            'apple_id' => 'apple-a', 'email' => 'stored@example.com', 'name' => 'Keep my name',
        ]);
        $other = User::factory()->create(['email' => 'other@example.com', 'apple_id' => 'apple-b']);
        $password = $user->password;
        $this->appleClaims(['email' => $email, 'email_verified' => $verified]);

        $this->postJson($this->authUrl('apple'), ['identity_token' => 'apple-token'])
            ->assertOk()->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'stored@example.com')
            ->assertJsonPath('data.user.name', 'Keep my name')
            ->assertJsonPath('data.requires_email_verification', ! $expectedVerified);

        $this->assertSame($expectedVerified, $user->fresh()->hasVerifiedEmail());
        $this->assertSame($password, $user->fresh()->password);
        $this->assertSame('apple-b', $other->fresh()->apple_id);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_verified_email_links_existing_google_and_password_account_without_overwriting_profile(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'customer@example.com', 'google_id' => 'google-a',
            'name' => 'Keep name', 'first_name' => 'Keep', 'last_name' => 'Name',
            'display_name' => 'Keep display name', 'password' => Hash::make('password123'),
        ]);
        $this->appleClaims(['email' => $user->email]);

        $this->postJson($this->authUrl('apple'), ['identity_token' => 'apple-token'])
            ->assertOk()->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.has_google_account', true)
            ->assertJsonPath('data.user.has_apple_account', true)
            ->assertJsonPath('data.user.name', 'Keep name')
            ->assertJsonPath('data.user.display_name', 'Keep display name')
            ->assertJsonPath('data.user.first_name', 'Keep')
            ->assertJsonPath('data.user.last_name', 'Name')
            ->assertJsonPath('data.requires_email_verification', false);

        $this->assertSame('apple-a', $user->fresh()->apple_id);
        $this->assertSame($user->password, $user->fresh()->password);
        $this->assertDatabaseCount('users', 1);
        $this->postJson($this->authUrl('login'), ['email' => $user->email, 'password' => 'password123'])
            ->assertOk()->assertJsonPath('data.user.id', $user->id);
    }

    public function test_a_different_apple_link_returns_conflict_without_changing_the_user(): void
    {
        $user = User::factory()->unverified()->create(['apple_id' => 'apple-b']);
        $this->appleClaims(['email' => $user->email]);

        $this->postJson($this->authUrl('apple'), ['identity_token' => 'apple-token'])
            ->assertConflict()->assertJsonPath('success', false);

        $this->assertSame('apple-b', $user->fresh()->apple_id);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame(0, $user->tokens()->count());
    }

    public static function unavailableAccounts(): array
    {
        $cases = [];
        foreach (['blocked' => 'Your account has been blocked.', 'inactive' => 'Your account is inactive.', 'pending' => 'Your account is pending approval.'] as $status => $message) {
            foreach ([null, 'apple-a'] as $appleId) {
                $cases[$status.' '.($appleId ?? 'unlinked')] = [$status, $message, $appleId];
            }
        }

        return $cases;
    }

    #[DataProvider('unavailableAccounts')]
    public function test_unavailable_accounts_are_rejected_before_linking_or_verification(string $status, string $message, ?string $appleId): void
    {
        $user = User::factory()->unverified()->create(['status' => $status, 'apple_id' => $appleId]);
        $this->appleClaims(['email' => $user->email]);

        $this->postJson($this->authUrl('apple'), ['identity_token' => 'apple-token'])
            ->assertForbidden()->assertJsonPath('message', $message);

        $this->assertSame($appleId, $user->fresh()->apple_id);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertNull($user->fresh()->last_login_at);
        $this->assertSame(0, $user->tokens()->count());
    }

    public static function untrustedEmails(): array
    {
        return ['missing' => [null, false], 'unverified' => ['customer@example.com', false]];
    }

    #[DataProvider('untrustedEmails')]
    public function test_new_link_requires_verified_email(?string $email, bool $verified): void
    {
        $user = User::factory()->unverified()->create(['email' => 'customer@example.com']);
        $this->appleClaims(['email' => $email, 'email_verified' => $verified]);

        $this->postJson($this->authUrl('apple'), ['identity_token' => 'apple-token', 'email' => $user->email])
            ->assertUnprocessable()->assertJsonPath('success', false);

        $this->assertNull($user->fresh()->apple_id);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_malformed_token_is_rejected_by_the_real_verifier_without_network_or_writes(): void
    {
        config(['services.apple.client_id' => 'com.burmeseshave.club']);

        $this->postJson($this->authUrl('apple'), ['identity_token' => 'not-a-jwt'])
            ->assertUnprocessable()->assertJsonPath('message', 'Unable to authenticate with Apple.');

        Http::assertNothingSent();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_request_requires_identity_token_and_validates_device_name(): void
    {
        $this->postJson($this->authUrl('apple'), ['email' => 'customer@example.com'])
            ->assertUnprocessable()->assertJsonValidationErrors('identity_token');
        $this->postJson($this->authUrl('apple'), ['identity_token' => ['bad'], 'device_name' => str_repeat('x', 256)])
            ->assertUnprocessable()->assertJsonValidationErrors(['identity_token', 'device_name']);
    }

    public function test_apple_id_is_unique_in_the_database(): void
    {
        User::factory()->create(['apple_id' => 'apple-a']);
        $this->expectException(UniqueConstraintViolationException::class);
        User::factory()->create(['apple_id' => 'apple-a']);
    }

    public function test_unique_constraint_conflict_rolls_back_and_returns_409(): void
    {
        $this->appleClaims();
        // Simulate the database rejecting a concurrent unique insertion, without mocking the ORM.
        DB::statement("CREATE TRIGGER reject_apple_link BEFORE INSERT ON users WHEN NEW.apple_id IS NOT NULL BEGIN SELECT RAISE(ABORT, 'UNIQUE constraint failed: users.apple_id'); END");

        $this->postJson($this->authUrl('apple'), ['identity_token' => 'apple-token'])
            ->assertConflict()->assertJsonPath('success', false);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_google_login_contract_and_existing_apple_link_are_preserved(): void
    {
        $user = User::factory()->unverified()->create(['apple_id' => 'apple-a']);
        $this->mock(GoogleAuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('verifyIdToken')->once()->with('google-token')->andReturn([
                'sub' => 'google-a', 'email' => $user->email, 'email_verified' => true,
                'name' => 'Google name',
            ]);
        });

        $this->postJson($this->authUrl('google'), ['id_token' => 'google-token'])
            ->assertOk()->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.has_google_account', true)
            ->assertJsonPath('data.user.has_apple_account', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.requires_email_verification', false);

        $this->assertSame('apple-a', $user->fresh()->apple_id);
        $this->assertSame(['customer'], $user->tokens()->sole()->abilities);
    }

    public function test_apple_only_user_can_delete_with_matching_subject_without_email(): void
    {
        $user = User::factory()->create(['apple_id' => 'apple-a']);
        $cart = $user->cart()->create();
        $this->appleClaims(['email' => null, 'email_verified' => false]);

        $this->withToken($user->createToken('Test')->plainTextToken)
            ->deleteJson($this->authUrl('account'), ['confirmation' => 'confirm', 'apple_identity_token' => 'apple-token'])
            ->assertOk()->assertJsonPath('message', 'Account deleted successfully.');

        $this->assertModelMissing($user);
        $this->assertModelMissing($cart);
        $this->assertSame(0, $user->tokens()->count());
        Http::assertNothingSent();
    }

    public static function invalidAppleDeletionCredentials(): array
    {
        return [
            'unlinked' => [null, 'apple-a', false],
            'different subject' => ['apple-a', 'apple-b', false],
            'invalid token' => ['apple-a', 'apple-a', true],
        ];
    }

    #[DataProvider('invalidAppleDeletionCredentials')]
    public function test_failed_apple_deletion_preserves_account_and_tokens(?string $appleId, string $subject, bool $invalid): void
    {
        $user = User::factory()->create(['apple_id' => $appleId]);
        $cart = $user->cart()->create();
        if ($appleId === null) {
            $this->mock(AppleAuthService::class)->shouldNotReceive('verifyIdToken');
        } elseif ($invalid) {
            $this->mock(AppleAuthService::class)->shouldReceive('verifyIdToken')->once()->andThrow(new RuntimeException('Invalid token'));
        } else {
            $this->appleClaims(['sub' => $subject]);
        }

        $this->withToken($user->createToken('Test')->plainTextToken)
            ->deleteJson($this->authUrl('account'), ['confirmation' => 'confirm', 'apple_identity_token' => 'apple-token'])
            ->assertUnprocessable()->assertJsonValidationErrors('apple_identity_token');

        $this->assertModelExists($user);
        $this->assertModelExists($cart);
        $this->assertSame(1, $user->tokens()->count());
    }

    public static function ambiguousDeletionCredentials(): array
    {
        return [
            'password and apple' => [['password' => 'password']],
            'google and apple' => [['google_id_token' => 'google-token']],
            'all three' => [['password' => 'password', 'google_id_token' => 'google-token']],
        ];
    }

    #[DataProvider('ambiguousDeletionCredentials')]
    public function test_apple_deletion_credentials_are_mutually_exclusive(array $credentials): void
    {
        $user = User::factory()->create(['apple_id' => 'apple-a']);
        $this->mock(AppleAuthService::class)->shouldNotReceive('verifyIdToken');

        $this->actingAs($user, 'sanctum')->deleteJson($this->authUrl('account'), array_merge($credentials, [
            'confirmation' => 'confirm', 'apple_identity_token' => 'apple-token',
        ]))->assertUnprocessable()->assertJsonValidationErrors('apple_identity_token');

        $this->assertModelExists($user);
    }

    public function test_apple_linked_user_can_still_delete_with_password(): void
    {
        $user = User::factory()->create(['apple_id' => 'apple-a', 'password' => Hash::make('password')]);

        $this->actingAs($user, 'sanctum')->deleteJson($this->authUrl('account'), [
            'confirmation' => 'confirm', 'password' => 'password',
        ])->assertOk();

        $this->assertModelMissing($user);
    }
}
