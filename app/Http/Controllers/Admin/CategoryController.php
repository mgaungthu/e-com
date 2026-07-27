<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('categories.view');

        $categories = Category::query()
            ->with('parent:id,name')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim((string) $request->input('search'));

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
                },
            )
            ->when(
                $request->filled('status'),
                function ($query) use ($request) {
                    $status = (string) $request->input('status');

                    if ($status === 'active') {
                        $query->where('is_active', true);
                    }

                    if ($status === 'inactive') {
                        $query->where('is_active', false);
                    }
                },
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(
                min(
                    max(
                        (int) $request->input('per_page', 15),
                        1,
                    ),
                    100,
                ),
            )
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $slug = filled($validated['slug'] ?? null)
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $slug = $this->generateUniqueSlug($slug);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request
                ->file('image')
                ->store('categories', 'public');
        }

        $category = Category::create([
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => [
                'category' => $this->categoryData(
                    $category->load('parent:id,name'),
                ),
            ],
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        Gate::authorize('categories.view');

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $this->categoryData(
                    $category->load('parent:id,name'),
                ),
            ],
        ]);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
    ): JsonResponse {
        $validated = $request->validated();

        $slug = filled($validated['slug'] ?? null)
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $slug = $this->generateUniqueSlug(
            $slug,
            $category->id,
        );

        $imagePath = $category->image_path;

        if ($request->boolean('remove_image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request
                ->file('image')
                ->store('categories', 'public');
        }

        $category->update([
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
        ]);

        $updatedCategory = $category
            ->fresh()
            ->load('parent:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => [
                'category' => $this->categoryData($updatedCategory),
            ],
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        Gate::authorize('categories.delete');

        if ($category->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This category has child categories and cannot be deleted.',
            ], 422);
        }

        /*
         * Soft delete ဖြစ်တဲ့အတွက် image file ကို မဖျက်သေးပါ။
         * Restore ပြန်လုပ်ရင် image မပျောက်အောင်ဖြစ်ပါတယ်။
         */
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }

    private function generateUniqueSlug(
        string $slug,
        ?int $ignoreId = null,
    ): string {
        $baseSlug = $slug !== ''
            ? Str::slug($slug)
            : Str::lower(Str::random(8));

        $candidate = $baseSlug;
        $counter = 2;

        while (
            Category::withTrashed()
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where(
                        'id',
                        '!=',
                        $ignoreId,
                    ),
                )
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $candidate;
    }

    private function categoryData(Category $category): array
    {
        return [
            'id' => $category->id,
            'parent_id' => $category->parent_id,
            'parent' => $category->parent
                ? [
                    'id' => $category->parent->id,
                    'name' => $category->parent->name,
                ]
                : null,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'image_path' => $category->image_path,
            'image_url' => $category->image_path
                ? asset('storage/'.$category->image_path)
                : null,
            'is_active' => (bool) $category->is_active,
            'sort_order' => (int) $category->sort_order,
            'seo_title' => $category->seo_title,
            'seo_description' => $category->seo_description,
            'created_at' => $category->created_at?->toISOString(),
            'updated_at' => $category->updated_at?->toISOString(),
        ];
    }
}