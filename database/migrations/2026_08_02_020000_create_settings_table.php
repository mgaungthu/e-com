<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string');
            $table->string('group', 50)->default('general');
            $table->timestamps();
            $table->index('group');
        });

        $now = now();
        DB::table('settings')->insert([
            ['key' => 'store_name', 'value' => 'E-commerce', 'type' => 'string', 'group' => 'general', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'support_email', 'value' => '', 'type' => 'string', 'group' => 'general', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'support_phone', 'value' => '', 'type' => 'string', 'group' => 'general', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'currency', 'value' => 'MMK', 'type' => 'string', 'group' => 'commerce', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'timezone', 'value' => 'Asia/Yangon', 'type' => 'string', 'group' => 'general', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'order_prefix', 'value' => 'ORD', 'type' => 'string', 'group' => 'commerce', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'default_low_stock_threshold', 'value' => '5', 'type' => 'integer', 'group' => 'inventory', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'tax_rate', 'value' => '0', 'type' => 'decimal', 'group' => 'commerce', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'shipping_fee', 'value' => '0', 'type' => 'decimal', 'group' => 'commerce', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
