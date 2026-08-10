<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
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
        $response = $this->postJson('/api/v1/auth/register', [
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

        $response = $this->postJson('/api/v1/auth/login', [
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

        $response = $this->postJson('/api/v1/auth/login', [
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
            ->getJson('/api/v1/auth/me');

        $response
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id);
    }
}
