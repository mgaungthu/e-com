<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->date('date_of_birth')->nullable();

            $table
                ->string('gender', 30)
                ->nullable();

            $table
                ->string('nationality', 100)
                ->nullable();

            $table
                ->string('preferred_language', 10)
                ->default('en');

            $table
                ->string('occupation', 150)
                ->nullable();

            $table
                ->string('company_name', 200)
                ->nullable();

            $table
                ->string('tax_id', 100)
                ->nullable();

            $table
                ->unsignedBigInteger('loyalty_points')
                ->default(0);

            $table
                ->unsignedInteger('total_orders')
                ->default(0);

            $table
                ->decimal('total_spent', 14, 2)
                ->default(0);

            $table
                ->timestamp('first_order_at')
                ->nullable();

            $table
                ->timestamp('last_order_at')
                ->nullable();

            $table
                ->boolean('marketing_email_enabled')
                ->default(true);

            $table
                ->boolean('marketing_sms_enabled')
                ->default(true);

            $table
                ->boolean('marketing_push_enabled')
                ->default(true);

            $table
                ->text('admin_note')
                ->nullable();

            $table->timestamps();

            $table->index('total_orders');
            $table->index('total_spent');
            $table->index('last_order_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};
