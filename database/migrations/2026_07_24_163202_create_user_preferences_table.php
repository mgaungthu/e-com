<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->char('currency', 3)
                ->default('THB');

            $table
                ->string('language', 10)
                ->default('en');

            $table
                ->string('timezone', 100)
                ->default('Asia/Bangkok');

            $table
                ->string('preferred_payment_method', 100)
                ->nullable();

            $table
                ->string('preferred_delivery_method', 100)
                ->nullable();

            $table
                ->boolean('notification_email')
                ->default(true);

            $table
                ->boolean('notification_sms')
                ->default(true);

            $table
                ->boolean('notification_push')
                ->default(true);

            $table
                ->boolean('dark_mode_enabled')
                ->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
