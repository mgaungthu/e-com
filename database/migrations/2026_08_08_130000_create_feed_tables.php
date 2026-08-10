<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('caption')->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'is_active', 'published_at']);
        });

        Schema::create('feed_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('image');
            $table->string('file_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();
            $table->unique(['feed_id', 'file_path']);
            $table->index(['feed_id', 'sort_order']);
        });

        Schema::create('feed_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['feed_id', 'product_id']);
            $table->index(['feed_id', 'sort_order']);
        });

        Schema::create('feed_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['feed_id', 'user_id']);
        });

        Schema::create('feed_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['feed_id', 'is_active', 'created_at']);
        });

        Schema::create('feed_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['feed_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_bookmarks');
        Schema::dropIfExists('feed_comments');
        Schema::dropIfExists('feed_likes');
        Schema::dropIfExists('feed_products');
        Schema::dropIfExists('feed_media');
        Schema::dropIfExists('feeds');
    }
};
