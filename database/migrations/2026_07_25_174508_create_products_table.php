<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();

            $table->string('short_description')->nullable();
            $table->text('description')->nullable();

            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();

            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);

            $table->string('image_path')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};