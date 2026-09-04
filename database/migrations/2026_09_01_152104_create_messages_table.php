<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('sender_type', 20);

            $table->unsignedBigInteger('sender_id');

            $table->string('type', 20)
                ->default('text');

            $table->text('message')
                ->nullable();

            $table->foreignId('feed_id')
                ->nullable()
                ->constrained('feeds')
                ->nullOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'conversation_id',
                'created_at',
            ]);

            $table->index([
                'sender_type',
                'sender_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'messages',
        );
    }
};