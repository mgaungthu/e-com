<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('device_id');

            $table
                ->string('platform', 30)
                ->nullable();

            $table
                ->string('device_name')
                ->nullable();

            $table
                ->string('device_model')
                ->nullable();

            $table
                ->string('os_version', 100)
                ->nullable();

            $table
                ->string('app_version', 100)
                ->nullable();

            $table
                ->text('push_token')
                ->nullable();

            $table
                ->timestamp('last_active_at')
                ->nullable();

            $table
                ->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->unique([
                'user_id',
                'device_id',
            ]);

            $table->index([
                'platform',
                'is_active',
            ]);

            $table->index('last_active_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
