<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductListResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = $request
            ->user()
            ->favouriteProducts()
            ->where('products.is_active', true)
            ->with([
                'category',
                'primaryImage',
            ])
            ->latest('product_favourites.created_at')
            ->get();

        $products->each(function (Product $product): void {
            $product->setAttribute('is_favourite', true);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => ProductListResource::collection($products),
            ],
        ]);
    }

    public function store(
        Request $request,
        Product $product,
    ): JsonResponse {
        $request
            ->user()
            ->favouriteProducts()
            ->syncWithoutDetaching([
                $product->id,
            ]);

        $product->load([
            'category',
            'primaryImage',
        ]);

        $product->setAttribute(
            'is_favourite',
            true,
        );

        return response()->json([
            'success' => true,
            'message' => 'Product added to favourites successfully.',
            'data' => [
                'product' => new ProductListResource($product),
            ],
        ]);
    }

    public function destroy(
        Request $request,
        Product $product,
    ): JsonResponse {
        $request
            ->user()
            ->favouriteProducts()
            ->detach($product->id);

        $product->load([
            'category',
            'primaryImage',
        ]);

        $product->setAttribute(
            'is_favourite',
            false,
        );

        return response()->json([
            'success' => true,
            'message' => 'Product removed from favourites successfully.',
            'data' => [
                'product' => new ProductListResource($product),
            ],
        ]);
    }
}