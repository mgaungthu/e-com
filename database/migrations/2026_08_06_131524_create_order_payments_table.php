<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('payment_method_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
             * Snapshot fields.
             * Payment method ကိုနောက်မှပြင်/ဖျက်လိုက်ရင်တောင်
             * order တင်ချိန်က information မပျောက်စေရန်။
             */
            $table->string('method_code', 50);
            $table->string('method_name', 100);

            $table->decimal('amount', 15, 2);

            $table->string('reference_number', 150)->nullable();

            $table->string('proof_image_path');

            $table->string('status', 30)
                ->default('submitted');

            $table->text('rejection_reason')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'order_id',
                'status',
            ]);

            $table->index([
                'status',
                'submitted_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
