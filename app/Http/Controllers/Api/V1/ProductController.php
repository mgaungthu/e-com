<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductDetailResource;
use App\Http\Resources\Api\V1\ProductListResource;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'in_stock' => ['nullable', 'boolean'],
            'featured' => ['nullable', 'boolean'],
            'sort' => [
                'nullable',
                Rule::in([
                    'newest',
                    'oldest',
                    'price_asc',
                    'price_desc',
                    'name_asc',
                    'name_desc',
                ]),
            ],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 15);

        $products = Product::query()
            ->where('is_active', true)
            ->with([
                'category:id,name,slug',
                'primaryImage',
            ])
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhere('short_description', 'like', '%'.$search.'%');
                });
            })
            ->when($validated['category'] ?? null, function (Builder $query, string $category): void {
                $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery
                    ->where('slug', $category)
                    ->where('is_active', true));
            })
            ->when(isset($validated['min_price']), fn (Builder $query) => $query->whereRaw('COALESCE(sale_price, price) >= ?', [$validated['min_price']]))
            ->when(isset($validated['max_price']), fn (Builder $query) => $query->whereRaw('COALESCE(sale_price, price) <= ?', [$validated['max_price']]))
            ->when(array_key_exists('in_stock', $validated)
                && $validated['in_stock'], fn (Builder $query) => $query->where('stock_quantity', '>', 0))
            ->when(array_key_exists('featured', $validated), fn (Builder $query) => $query->where('is_featured', $validated['featured']))
            ->tap(function (Builder $query) use ($validated): void {
                match ($validated['sort'] ?? 'newest') {
                    'oldest' => $query->oldest(),
                    'price_asc' => $query
                        ->orderByRaw('COALESCE(sale_price, price) ASC'),
                    'price_desc' => $query
                        ->orderByRaw('COALESCE(sale_price, price) DESC'),
                    'name_asc' => $query->orderBy('name'),
                    'name_desc' => $query->orderByDesc('name'),
                    default => $query->latest(),
                };
            })
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginatedResponse(paginator: $products, resourceClass: ProductListResource::class, message: 'Products retrieved successfully.');
    }

    public function featured(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $limit = (int) ($validated['limit'] ?? 10);

        $products = Product::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with([
                'category:id,name,slug',
                'primaryImage',
            ])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $this->successResponse(data: [
            'products' => ProductListResource::collection($products),
        ], message: 'Featured products retrieved successfully.', );
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'category:id,name,slug,description,image_path',
                'images',
            ])
            ->first();

        if (! $product) {
            return $this->errorResponse(message: 'Product not found.', status: 404);
        }

        return $this->successResponse(data: [
            'product' => new ProductDetailResource($product),
        ], message: 'Product retrieved successfully.', );
    }

    private function paginatedResponse(mixed $paginator, string $resourceClass, string $message): JsonResponse
    {
        return $this->successResponse(data: [
            'products' => $resourceClass::collection($paginator->getCollection()),

            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],

            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], message: $message, );
    }
}
