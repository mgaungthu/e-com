<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table
                ->string('type', 30)
                ->default('ewallet')
                ->after('code');

            $table
                ->boolean('requires_proof')
                ->default(true)
                ->after('instructions');
        });

        /*
        |--------------------------------------------------------------------------
        | Backfill Existing Payment Methods
        |--------------------------------------------------------------------------
        */

        DB::table('payment_methods')
            ->where('code', 'cod')
            ->update([
                'type' => 'cod',
                'requires_proof' => false,
            ]);

        DB::table('payment_methods')
            ->whereIn('code', [
                'kbzpay',
                'ayapay',
                'wavepay',
            ])
            ->update([
                'type' => 'ewallet',
                'requires_proof' => true,
            ]);
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'requires_proof',
            ]);
        });
    }
};