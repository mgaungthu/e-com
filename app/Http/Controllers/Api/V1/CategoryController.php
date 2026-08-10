<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ProductListResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with([
                'children' => fn ($query) => $query
                    ->where('is_active', true)
                    ->withCount([
                        'products' => fn ($productQuery) => $productQuery
                            ->where('is_active', true),
                    ])
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ])
            ->withCount([
                'products' => fn ($query) => $query
                    ->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->successResponse(data: [
            'categories' => CategoryResource::collection($categories),
        ], message: 'Categories retrieved successfully.', );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'parent:id,name,slug',
                'children' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ])
            ->withCount([
                'products' => fn ($query) => $query
                    ->where('is_active', true),
            ])
            ->first();

        if (! $category) {
            return $this->errorResponse(message: 'Category not found.', status: 404);
        }

        $perPage = min(max((int) $request->integer('per_page', 15), 1), 50);

        $products = $category->products()
            ->where('is_active', true)
            ->with([
                'category:id,name,slug',
                'primaryImage',
            ])
            ->orderByDesc('is_featured')
            ->latest()
            ->paginate($perPage);

        return $this->successResponse(data: [
            'category' => new CategoryResource($category),

            'products' => ProductListResource::collection($products->getCollection()),

            'meta' => [
                'current_page' => $products->currentPage(),
                'from' => $products->firstItem(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'to' => $products->lastItem(),
                'total' => $products->total(),
            ],

            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ],
        ], message: 'Category retrieved successfully.', );
    }
}
