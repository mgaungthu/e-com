<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Services\Auth\GoogleAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('customer');
    }

    private function authUrl(string $path): string
    {
        return 'https://'.config('app.api_domain').'/v1/auth/'.$path;
    }

    public function test_customer_can_register(): void
    {
        $response = $this->postJson($this->authUrl('register'), [
            'first_name' => 'Aung',
            'last_name' => 'Thu',
            'email' => 'aung@example.com',
            'phone' => '09123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'Test Device',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'aung@example.com')
            ->assertJsonPath('data.user.email_verified_at', null)
            ->assertJsonPath('data.requires_email_verification', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'aung@example.com',
            'phone' => '09123456789',
            'role' => 'customer',
            'status' => 'active',
        ]);
    }

    public function test_customer_registration_requires_a_phone_number(): void
    {
        $response = $this->postJson($this->authUrl('register'), [
            'first_name' => 'Aung',
            'email' => 'aung@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('phone');
    }

    public function test_customer_can_login(): void
    {
        User::factory()->create([
            'email' => 'aung@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'status' => 'active',
        ]);

        $response = $this->postJson($this->authUrl('login'), [
            'email' => 'aung@example.com',
            'password' => 'password123',
            'device_name' => 'Test Device',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'token',
                    'token_type',
                ],
            ]);
    }

    public function test_blocked_customer_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'blocked@example.com',
            'password' => Hash::make('password123'),
            'status' => 'blocked',
        ]);

        $response = $this->postJson($this->authUrl('login'), [
            'email' => 'blocked@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_authenticated_customer_can_get_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);

        $token = $user->createToken('Test Device')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->getJson($this->authUrl('me'));

        $response
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_account_deletion_requires_authentication(): void
    {
        $this->deleteJson($this->authUrl('account'), [
            'confirmation' => 'confirm',
            'password' => 'password',
        ])->assertUnauthorized();
    }

    public static function invalidDeletionPayloads(): array
    {
        return [
            'missing confirmation' => [['password' => 'password'], 'confirmation'],
            'capitalized' => [['confirmation' => 'Confirm', 'password' => 'password'], 'confirmation'],
            'uppercase' => [['confirmation' => 'CONFIRM', 'password' => 'password'], 'confirmation'],
            'suffix' => [['confirmation' => 'confirm123', 'password' => 'password'], 'confirmation'],
            'missing credential' => [['confirmation' => 'confirm'], 'password'],
            'wrong password' => [['confirmation' => 'confirm', 'password' => 'wrong'], 'password'],
            'ambiguous credentials' => [['confirmation' => 'confirm', 'password' => 'password', 'google_id_token' => 'token'], 'password'],
        ];
    }

    #[DataProvider('invalidDeletionPayloads')]
    public function test_invalid_deletion_preserves_user_tokens_and_owned_data(array $payload, string $field): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $token = $user->createToken('Test Device')->plainTextToken;
        $cart = $user->cart()->create();

        $this->withToken($token)->deleteJson($this->authUrl('account'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors($field);

        $this->assertModelExists($user);
        $this->assertModelExists($cart);
        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_password_deletion_cleans_up_account_and_retains_order_history(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/test.jpg', 'avatar');
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'avatar_path' => 'avatars/test.jpg',
        ]);
        $other = User::factory()->create();
        $other->createToken('Other Device');
        $user->assignRole('customer');
        $user->givePermissionTo(Permission::findOrCreate('test-permission'));
        $token = $user->createToken('Test Device')->plainTextToken;
        $user->createToken('Second Device');
        $cart = $user->cart()->create();
        $user->preference()->create();
        $user->addresses()->create([
            'recipient_name' => 'Customer', 'phone' => '09123456789',
            'address_line_one' => 'Test street', 'city' => 'Yangon',
        ]);
        foreach (['password_reset_codes', 'email_verification_codes'] as $table) {
            DB::table($table)->insert([
                'user_id' => $user->id, 'code_hash' => 'hash', 'expires_at' => now()->addMinutes(10),
            ]);
        }
        $user->devices()->create(['device_id' => 'device', 'platform' => 'ios', 'push_token' => 'ExponentPushToken[test]']);
        $conversation = $user->conversations()->create();
        DB::table('sessions')->insert([
            'id' => 'test-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time(),
        ]);
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'reset-token']);
        $user->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test', 'data' => []]);

        $snapshot = json_encode(['name' => 'Customer', 'address' => 'Historical address']);
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'DELETE-TEST', 'user_id' => $user->id,
            'shipping_address' => $snapshot, 'billing_address' => $snapshot,
        ]);
        DB::table('order_items')->insert([
            'order_id' => $orderId, 'product_name' => 'Razor', 'sku' => 'TEST',
            'unit_price' => 100, 'quantity' => 1, 'line_total' => 100,
        ]);
        DB::table('order_status_histories')->insert(['order_id' => $orderId, 'to_status' => 'pending']);
        DB::table('order_payments')->insert([
            'order_id' => $orderId, 'method_code' => 'test', 'method_name' => 'Test',
            'amount' => 100, 'proof_image_path' => 'proof.jpg',
        ]);
        $history = [];
        foreach (['orders', 'order_items', 'order_status_histories', 'order_payments'] as $table) {
            $history[$table] = (array) DB::table($table)->first();
        }

        $this->withToken($token)->deleteJson($this->authUrl('account'), [
            'confirmation' => ' confirm ', 'password' => 'password',
        ])->assertOk()->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Account deleted successfully.');

        $this->assertModelMissing($user);
        $this->assertModelMissing($cart);
        $this->assertModelMissing($conversation);
        foreach (['user_preferences', 'user_devices', 'sessions', 'addresses', 'password_reset_codes', 'email_verification_codes'] as $table) {
            $this->assertDatabaseMissing($table, ['user_id' => $user->id]);
        }
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id, 'tokenable_type' => $user->getMorphClass()]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $user->id, 'notifiable_type' => $user->getMorphClass()]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        foreach (['model_has_roles', 'model_has_permissions'] as $table) {
            $this->assertDatabaseMissing($table, ['model_id' => $user->id, 'model_type' => $user->getMorphClass()]);
        }
        $history['orders']['user_id'] = null;
        foreach ($history as $table => $row) {
            $this->assertSame($row, (array) DB::table($table)->first());
        }
        Storage::disk('public')->assertMissing('avatars/test.jpg');
        $this->assertModelExists($other);
        $this->assertSame(1, $other->tokens()->count());
    }

    public function test_google_linked_user_can_delete_with_password(): void
    {
        $user = User::factory()->create(['google_id' => 'google-a', 'password' => Hash::make('password')]);
        $this->withToken($user->createToken('Test')->plainTextToken)
            ->deleteJson($this->authUrl('account'), ['confirmation' => 'confirm', 'password' => 'password'])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertModelMissing($user);
    }

    public function test_google_user_can_delete_with_matching_google_token(): void
    {
        $user = User::factory()->create(['google_id' => 'google-a']);
        $this->mock(GoogleAuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('verifyIdToken')->once()->with('valid-token')
                ->andReturn(['sub' => 'google-a', 'email' => ' '.strtoupper($user->email).' ']);
        });
        $this->withToken($user->createToken('Test')->plainTextToken)
            ->deleteJson($this->authUrl('account'), ['confirmation' => 'confirm', 'google_id_token' => 'valid-token'])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertModelMissing($user);
        $this->assertSame(0, $user->tokens()->count());
    }

    public static function invalidGoogleCredentials(): array
    {
        return [
            'other subject' => ['google-a', 'google-b', 'same', false],
            'other email' => ['google-a', 'google-a', 'other@example.com', false],
            'unlinked' => [null, 'google-a', 'same', false],
            'invalid token' => ['google-a', 'google-a', 'same', true],
        ];
    }

    #[DataProvider('invalidGoogleCredentials')]
    public function test_google_verification_failure_preserves_account(?string $googleId, string $subject, string $email, bool $invalid): void
    {
        $user = User::factory()->create(['google_id' => $googleId]);
        $cart = $user->cart()->create();
        $this->mock(GoogleAuthService::class, function ($mock) use ($user, $googleId, $subject, $email, $invalid) {
            if ($googleId === null) {
                $mock->shouldNotReceive('verifyIdToken');
            } elseif ($invalid) {
                $mock->shouldReceive('verifyIdToken')->once()->andThrow(new \RuntimeException('Invalid token'));
            } else {
                $mock->shouldReceive('verifyIdToken')->once()
                    ->andReturn(['sub' => $subject, 'email' => $email === 'same' ? $user->email : $email]);
            }
        });
        $this->withToken($user->createToken('Test')->plainTextToken)
            ->deleteJson($this->authUrl('account'), ['confirmation' => 'confirm', 'google_id_token' => 'token'])
            ->assertUnprocessable()->assertJsonValidationErrors('google_id_token');
        $this->assertModelExists($user);
        $this->assertModelExists($cart);
        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_database_failure_rolls_back_account_cleanup(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $cart = $user->cart()->create();
        $token = $user->createToken('Test')->plainTextToken;
        DB::statement("CREATE TRIGGER prevent_user_deletion BEFORE DELETE ON users BEGIN SELECT RAISE(ABORT, 'test failure'); END");

        $this->withToken($token)->deleteJson($this->authUrl('account'), [
            'confirmation' => 'confirm', 'password' => 'password',
        ])->assertStatus(500);

        $this->assertModelExists($user);
        $this->assertModelExists($cart);
        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_avatar_storage_failure_does_not_fail_successful_deletion(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'), 'avatar_path' => 'avatars/test.jpg',
        ]);
        Storage::shouldReceive('disk')->once()->with('public')->andReturnSelf();
        Storage::shouldReceive('delete')->once()->with('avatars/test.jpg')
            ->andThrow(new \RuntimeException('Storage unavailable'));

        $this->withToken($user->createToken('Test')->plainTextToken)
            ->deleteJson($this->authUrl('account'), ['confirmation' => 'confirm', 'password' => 'password'])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertModelMissing($user);
    }

    public function test_profile_exposes_only_google_link_availability(): void
    {
        $user = User::factory()->create(['google_id' => 'google-a']);
        $this->withToken($user->createToken('Test')->plainTextToken)->getJson($this->authUrl('me'))
            ->assertOk()->assertJsonPath('data.user.has_google_account', true)
            ->assertJsonMissingPath('data.user.google_id')->assertJsonMissingPath('data.user.password');
    }
}
