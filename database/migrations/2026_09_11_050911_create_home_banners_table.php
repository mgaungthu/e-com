<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_banners', function (Blueprint $table) {
            $table->id();

            $table->string('badge', 50)->nullable();

            $table->string('title', 100);
            $table->string('highlight', 100)->nullable();

            $table->string('meta', 120)->nullable();

            $table->string('cta_label', 40)
                ->default('SHOP NOW');

            $table->string('artwork_path');

            $table->decimal(
                'artwork_opacity',
                3,
                2
            )->default(0.80);

            $table->string(
                'background_color',
                7
            )->default('#080909');

            $table->string(
                'accent_color',
                7
            )->default('#F3DB25');

            $table->string(
                'destination_type',
                20
            )->default('shop');

            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamp('starts_at')
                ->nullable();

            $table->timestamp('ends_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'is_active',
                'sort_order',
            ]);

            $table->index([
                'starts_at',
                'ends_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'home_banners'
        );
    }
};