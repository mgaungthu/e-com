<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            [
                'email' => 'admin@example.com',
            ],
            [
                'name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'display_name' => 'Super Admin',
                'password' => 'password123',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );

        $admin->syncRoles([
            'super_admin',
        ]);
    }
}
