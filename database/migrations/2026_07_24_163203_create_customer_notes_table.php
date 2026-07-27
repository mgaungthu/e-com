<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_notes', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('note');

            $table
                ->string('visibility', 30)
                ->default('internal');

            $table
                ->boolean('is_pinned')
                ->default(false);

            $table->timestamps();

            $table->index([
                'user_id',
                'visibility',
            ]);

            $table->index([
                'user_id',
                'is_pinned',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_notes');
    }
};
