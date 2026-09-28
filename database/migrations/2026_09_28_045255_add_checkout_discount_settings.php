<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('settings')->insert([
            [
                'key' => 'checkout_discount_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'commerce',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'checkout_discount_percent',
                'value' => '20',
                'type' => 'decimal',
                'group' => 'commerce',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->whereIn('key', [
                'checkout_discount_enabled',
                'checkout_discount_percent',
            ])
            ->delete();
    }
};