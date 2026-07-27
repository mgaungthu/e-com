<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table
                ->string('first_name')
                ->nullable()
                ->after('name');

            $table
                ->string('last_name')
                ->nullable()
                ->after('first_name');

            $table
                ->string('display_name')
                ->nullable()
                ->after('last_name');

            $table
                ->string('phone', 30)
                ->nullable()
                ->unique()
                ->after('email');

            $table
                ->timestamp('phone_verified_at')
                ->nullable()
                ->after('email_verified_at');

            $table
                ->string('avatar_path')
                ->nullable()
                ->after('password');

            $table
                ->string('status', 30)
                ->default('active')
                ->index()
                ->after('avatar_path');

            $table
                ->timestamp('last_login_at')
                ->nullable()
                ->after('status');

            $table
                ->string('last_login_ip', 45)
                ->nullable()
                ->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_phone_unique');
            $table->dropIndex('users_status_index');

            $table->dropColumn([
                'first_name',
                'last_name',
                'display_name',
                'phone',
                'phone_verified_at',
                'avatar_path',
                'status',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
