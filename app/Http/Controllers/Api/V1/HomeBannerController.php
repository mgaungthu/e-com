<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HomeBanner;
use Illuminate\Http\JsonResponse;

class HomeBannerController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $now = now();

        $banners = HomeBanner::query()
            ->with([
                'product:id,slug,is_active',
            ])

            /*
            |--------------------------------------------------------------------------
            | Banner must be active
            |--------------------------------------------------------------------------
            */

            ->where(
                'is_active',
                true
            )

            /*
            |--------------------------------------------------------------------------
            | Start Schedule
            |--------------------------------------------------------------------------
            |
            | starts_at = null
            | means the banner can be shown immediately.
            |
            */

            ->where(
                function ($query) use ($now) {
                    $query
                        ->whereNull('starts_at')
                        ->orWhere(
                            'starts_at',
                            '<=',
                            $now
                        );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | End Schedule
            |--------------------------------------------------------------------------
            |
            | ends_at = null
            | means there is no expiration date.
            |
            */

            ->where(
                function ($query) use ($now) {
                    $query
                        ->whereNull('ends_at')
                        ->orWhere(
                            'ends_at',
                            '>=',
                            $now
                        );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | Product must still be usable
            |--------------------------------------------------------------------------
            |
            | Every banner points to a product.
            |
            | If the product is disabled or soft-deleted,
            | the banner must not be sent to the mobile app.
            |
            */

            ->whereHas(
                'product',
                fn ($query) => $query->where(
                    'is_active',
                    true
                )
            )

            /*
            |--------------------------------------------------------------------------
            | Carousel Order
            |--------------------------------------------------------------------------
            */

            ->orderBy('sort_order')
            ->orderBy('id')

            ->get();

        return response()->json([
            'success' => true,

            'data' => [
                'banners' => $banners
                    ->map(
                        fn (HomeBanner $banner) => [
                            'id' => $banner->id,

                            'image_url' => $banner->image_url,

                            'product' => [
                                'id' => $banner->product->id,
                                'slug' => $banner->product->slug,
                            ],
                        ]
                    )
                    ->values(),
            ],
        ]);
    }
}