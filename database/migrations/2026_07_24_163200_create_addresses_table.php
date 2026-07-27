<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->string('type', 30)
                ->default('home');

            $table
                ->string('label', 100)
                ->nullable();

            $table->string('recipient_name');

            $table
                ->string('phone', 30);

            $table
                ->string('alternate_phone', 30)
                ->nullable();

            $table->string('address_line_one');

            $table
                ->string('address_line_two')
                ->nullable();

            $table
                ->string('building')
                ->nullable();

            $table
                ->string('floor', 50)
                ->nullable();

            $table
                ->string('unit', 50)
                ->nullable();

            $table
                ->string('landmark')
                ->nullable();

            $table
                ->string('township', 150)
                ->nullable();

            $table->string('city', 150);

            $table
                ->string('state', 150)
                ->nullable();

            $table
                ->string('postal_code', 30)
                ->nullable();

            $table
                ->char('country_code', 2)
                ->default('TH');

            $table
                ->decimal('latitude', 10, 7)
                ->nullable();

            $table
                ->decimal('longitude', 10, 7)
                ->nullable();

            $table
                ->text('delivery_instruction')
                ->nullable();

            $table
                ->boolean('is_default_shipping')
                ->default(false);

            $table
                ->boolean('is_default_billing')
                ->default(false);

            $table->timestamps();

            $table->index([
                'user_id',
                'is_default_shipping',
            ]);

            $table->index([
                'user_id',
                'is_default_billing',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
