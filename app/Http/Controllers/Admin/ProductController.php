<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with([
                'category:id,name',
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

        if ($request->hasFile('image')) {
            $data['image_path'] = $request
                ->file('image')
                ->store('products', 'public');
        }

        unset(
            $data['image'],
            $data['remove_image'],
        );

        $product = Product::query()->create($data);

        $product->load('category:id,name');

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
        $product->load('category:id,name');

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

        if ($request->boolean('remove_image')) {
            $this->deleteImage($product->image_path);
            $data['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($product->image_path);

            $data['image_path'] = $request
                ->file('image')
                ->store('products', 'public');
        }

        unset(
            $data['image'],
            $data['remove_image']
        );

        $product->update($data);

        $product->load('category:id,name');

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

    private function deleteImage(?string $imagePath): void
    {
        if (! $imagePath) {
            return;
        }

        Storage::disk('public')->delete($imagePath);
    }
}
