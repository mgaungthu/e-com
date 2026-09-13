<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Safety Check
        |--------------------------------------------------------------------------
        |
        | New banner structure requires every banner to have a product.
        |
        | If old shop/category banners already exist without product_id,
        | stop the migration instead of silently deleting data.
        |
        */

        if (
            DB::table('home_banners')
                ->whereNull('product_id')
                ->exists()
        ) {
            throw new \RuntimeException(
                'Cannot simplify home_banners because one or more existing banners do not have a product_id.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Remove old foreign keys first
        |--------------------------------------------------------------------------
        */

        Schema::table('home_banners', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['category_id']);
        });

        /*
        |--------------------------------------------------------------------------
        | Rename artwork_path -> image_path
        |--------------------------------------------------------------------------
        */

        Schema::table('home_banners', function (Blueprint $table) {
            $table->renameColumn(
                'artwork_path',
                'image_path'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Remove unused fields
        |--------------------------------------------------------------------------
        */

        Schema::table('home_banners', function (Blueprint $table) {
            $table->dropColumn([
                'badge',
                'title',
                'highlight',
                'meta',
                'cta_label',

                'artwork_opacity',

                'background_color',
                'accent_color',

                'destination_type',

                'category_id',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Product is now required
        |--------------------------------------------------------------------------
        */

        Schema::table('home_banners', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')
                ->nullable(false)
                ->change();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Restore product relation behavior
        |--------------------------------------------------------------------------
        */

        Schema::table('home_banners', function (Blueprint $table) {
            $table->dropForeign(['product_id']);

            $table->unsignedBigInteger('product_id')
                ->nullable()
                ->change();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Restore old fields
        |--------------------------------------------------------------------------
        */

        Schema::table('home_banners', function (Blueprint $table) {
            $table->string('badge', 50)
                ->nullable();

            /*
             * Nullable here makes rollback safe for banners that were created
             * using the simplified schema.
             */
            $table->string('title', 100)
                ->nullable();

            $table->string('highlight', 100)
                ->nullable();

            $table->string('meta', 120)
                ->nullable();

            $table->string('cta_label', 40)
                ->default('SHOP NOW');

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

            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | Rename image_path -> artwork_path
        |--------------------------------------------------------------------------
        */

        Schema::table('home_banners', function (Blueprint $table) {
            $table->renameColumn(
                'image_path',
                'artwork_path'
            );
        });
    }
};