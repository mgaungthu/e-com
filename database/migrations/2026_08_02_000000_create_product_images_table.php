<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'is_primary']);
            $table->index(['product_id', 'sort_order']);
        });

        $now = now();

        DB::table('products')
            ->whereNotNull('image_path')
            ->orderBy('id')
            ->select(['id', 'name', 'image_path'])
            ->each(function (object $product) use ($now): void {
                DB::table('product_images')->insert([
                    'product_id' => $product->id,
                    'path' => $product->image_path,
                    'alt_text' => $product->name,
                    'is_primary' => true,
                    'sort_order' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
