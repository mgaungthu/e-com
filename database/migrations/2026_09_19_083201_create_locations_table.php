<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('parent_id')
                ->nullable()
                ->constrained('locations')
                ->restrictOnDelete();

            $table->string('name_en', 150);

            $table
                ->string('name_mm', 150)
                ->nullable();

            $table->string('type', 30);

            /*
             * null ဆိုရင် parent location ရဲ့ shipping fee ကို inherit လုပ်မယ်။
             * Parent တွေအကုန် null ဖြစ်နေရင် settings.shipping_fee ကို
             * default fallback အဖြစ်သုံးမယ်။
             */
            $table
                ->decimal('shipping_fee', 12, 2)
                ->nullable();

            $table
                ->boolean('is_active')
                ->default(true);

            $table
                ->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->index([
                'parent_id',
                'is_active',
            ]);

            $table->index([
                'type',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};