<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private readonly ProductImageService $productImageService) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('products.view');

        $query = Product::query()
            ->with([
                'category:id,name',
                'primaryImage',
            ])
            ->latest();

        $search = trim((string) $request->input('search'));

        if ($search !== '') {
            $query->where(function ($productQuery) use ($search) {
                $productQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('status')) {
            match ($request->string('status')->toString()) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                default => null,
            };
        }

        if ($request->filled('stock_status')) {
            match ($request->string('stock_status')->toString()) {
                'in_stock' => $query
                    ->whereColumn('stock_quantity', '>', 'low_stock_threshold'),

                'low_stock' => $query
                    ->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '<=', 'low_stock_threshold'),

                'out_of_stock' => $query
                    ->where('stock_quantity', '<=', 0),

                default => null,
            };
        }

        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return response()->json([
            'success' => true,
            'data' => $query->paginate($perPage),
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['slug'] = $this->generateSlug($data['slug'] ?? null, $data['name']);

        $data['is_active'] = $request->boolean('is_active');

        $data['is_featured'] = $request->boolean('is_featured');

        unset(
            $data['images'],
            $data['removed_image_ids'],
            $data['image_order'],
            $data['primary_image_id'],
            $data['primary_new_image_index'],
        );

        $product = DB::transaction(function () use ($data, $request): Product {
            $product = Product::query()->create($data);

            $this->syncImages($product, $request);

            return $product;
        });

        $product->load(['category:id,name', 'images', 'primaryImage']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => [
                'product' => $product,
            ],
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        Gate::authorize('products.view');

        $product->load(['category:id,name', 'images', 'primaryImage']);

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
            ],
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        $data['slug'] = $this->generateSlug($data['slug'] ?? null, $data['name'], $product->id);

        $data['is_active'] = $request->boolean('is_active', false);

        $data['is_featured'] = $request->boolean('is_featured', false);

        unset(
            $data['images'],
            $data['removed_image_ids'],
            $data['image_order'],
            $data['primary_image_id'],
            $data['primary_new_image_index'],
        );

        DB::transaction(function () use ($data, $product, $request): void {
            $product->update($data);
            $this->syncImages($product, $request);
        });

        $product->load(['category:id,name', 'images', 'primaryImage']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => [
                'product' => $product,
            ],
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        Gate::authorize('products.delete');

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    private function generateSlug(?string $requestedSlug, string $name, ?int $ignoreProductId = null): string
    {
        $baseSlug = Str::slug($requestedSlug ?: $name);

        if ($baseSlug === '') {
            $baseSlug = 'product';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Product::query()
                ->when($ignoreProductId, fn ($query) => $query->where('id', '!=', $ignoreProductId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function syncImages(Product $product, Request $request): void
    {
        $images = $product->images()->get();
        $removedIds = collect($request->input('removed_image_ids', []))
            ->map(fn (mixed $id): int => (int) $id);

        foreach ($images->whereIn('id', $removedIds) as $image) {
            $this->productImageService->delete($image->path);

            $image->delete();
        }

        $remainingImages = $product->images()->get()->keyBy('id');
        $requestedOrder = collect($request->input('image_order', []))
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $remainingImages->has($id));

        $orderedImages = $requestedOrder
            ->map(fn (int $id): ProductImage => $remainingImages->get($id))
            ->concat($remainingImages->except($requestedOrder->all())->values());

        foreach ($orderedImages as $index => $image) {
            $image->update(['sort_order' => $index]);
        }

        $newImages = collect();
        $nextSortOrder = $orderedImages->count();

        foreach ($request->file('images', []) as $index => $file) {
            $path = $this->productImageService->store($file);

            $newImages->put($index, $product->images()->create([
                'path' => $path,
                'alt_text' => $product->name,
                'is_primary' => false,
                'sort_order' => $nextSortOrder++,
            ]), );
        }

        $primaryImage = null;
        $primaryImageId = $request->integer('primary_image_id');
        $primaryNewImageIndex = $request->input('primary_new_image_index');

        if ($primaryImageId > 0) {
            $primaryImage = $product->images()->find($primaryImageId);
        }

        if ($primaryImage === null && $primaryNewImageIndex !== null) {
            $primaryImage = $newImages->get((int) $primaryNewImageIndex);
        }

        if ($primaryImage === null) {
            $primaryImage = $product->images()
                ->where('is_primary', true)
                ->first() ?? $product->images()->first();
        }

        $product->images()->update(['is_primary' => false]);

        if ($primaryImage !== null) {
            $primaryImage->update(['is_primary' => true]);
        }

        $product->update([
            'image_path' => $primaryImage?->path,
        ]);
    }
}
