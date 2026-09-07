<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table
                ->string('image_path')
                ->nullable()
                ->after('message');

            $table
                ->unsignedInteger('image_width')
                ->nullable()
                ->after('image_path');

            $table
                ->unsignedInteger('image_height')
                ->nullable()
                ->after('image_width');

            $table
                ->unsignedBigInteger('image_size')
                ->nullable()
                ->after('image_height');

            $table
                ->string('image_mime_type', 100)
                ->nullable()
                ->after('image_size');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn([
                'image_path',
                'image_width',
                'image_height',
                'image_size',
                'image_mime_type',
            ]);
        });
    }
};