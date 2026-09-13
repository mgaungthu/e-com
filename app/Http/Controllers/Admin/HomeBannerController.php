<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeBanner\StoreHomeBannerRequest;
use App\Http\Requests\Admin\HomeBanner\UpdateHomeBannerRequest;
use App\Models\HomeBanner;
use App\Services\HomeBannerImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class HomeBannerController extends Controller
{
    public function __construct(
        private readonly HomeBannerImageService $imageService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('home_banners.view');

        $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $banners = HomeBanner::query()
            ->with([
                'product:id,name,slug,is_active',
            ])
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim(
                        (string) $request->input('search')
                    );

                    $query->whereHas(
                        'product',
                        function ($productQuery) use ($search) {
                            $productQuery
                                ->where(
                                    'name',
                                    'like',
                                    '%'.$search.'%'
                                )
                                ->orWhere(
                                    'slug',
                                    'like',
                                    '%'.$search.'%'
                                );
                        }
                    );
                }
            )
            ->when(
                $request->has('is_active'),
                fn ($query) => $query->where(
                    'is_active',
                    $request->boolean('is_active')
                )
            )
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(
                $request->integer(
                    'per_page',
                    15
                )
            );

        return response()->json([
            'success' => true,
            'data' => $banners,
        ]);
    }

    public function show(
        HomeBanner $homeBanner
    ): JsonResponse {
        Gate::authorize('home_banners.view');

        return response()->json([
            'success' => true,

            'data' => [
                'banner' => $this->loadBanner(
                    $homeBanner
                ),
            ],
        ]);
    }

    public function store(
        StoreHomeBannerRequest $request
    ): JsonResponse {
        Gate::authorize('home_banners.create');

        $data = $request->validated();

        $imagePath = null;

        try {
            $imagePath = $this->imageService->store(
                $request->file('image')
            );

            $banner = DB::transaction(
                function () use (
                    $request,
                    $data,
                    $imagePath
                ): HomeBanner {
                    return HomeBanner::query()->create([
                        'image_path' => $imagePath,

                        'product_id' => $data['product_id'],

                        'sort_order' => $data['sort_order'] ?? 0,

                        'is_active' => $request->boolean(
                            'is_active'
                        ),

                        'starts_at' => $data['starts_at'] ?? null,

                        'ends_at' => $data['ends_at'] ?? null,
                    ]);
                }
            );
        } catch (Throwable $exception) {
            if ($imagePath) {
                $this->imageService->delete(
                    $imagePath
                );
            }

            throw $exception;
        }

        return response()->json([
            'success' => true,

            'message' => 'Home banner created successfully.',

            'data' => [
                'banner' => $this->loadBanner(
                    $banner
                ),
            ],
        ], 201);
    }

    public function update(
        UpdateHomeBannerRequest $request,
        HomeBanner $homeBanner
    ): JsonResponse {
        Gate::authorize('home_banners.update');

        $data = $request->validated();

        $oldImagePath = $homeBanner->image_path;
        $newImagePath = null;

        try {
            if ($request->hasFile('image')) {
                $newImagePath = $this->imageService->store(
                    $request->file('image')
                );
            }

            DB::transaction(
                function () use (
                    $request,
                    $homeBanner,
                    $data,
                    $newImagePath
                ): void {
                    $homeBanner->update([
                        'image_path' => $newImagePath
                            ?? $homeBanner->image_path,

                        'product_id' => $data['product_id'],

                        'sort_order' => $data['sort_order'] ?? 0,

                        'is_active' => $request->boolean(
                            'is_active'
                        ),

                        'starts_at' => $data['starts_at'] ?? null,

                        'ends_at' => $data['ends_at'] ?? null,
                    ]);
                }
            );
        } catch (Throwable $exception) {
            if ($newImagePath) {
                $this->imageService->delete(
                    $newImagePath
                );
            }

            throw $exception;
        }

        if (
            $newImagePath
            && $newImagePath !== $oldImagePath
        ) {
            $this->imageService->delete(
                $oldImagePath
            );
        }

        return response()->json([
            'success' => true,

            'message' => 'Home banner updated successfully.',

            'data' => [
                'banner' => $this->loadBanner(
                    $homeBanner->refresh()
                ),
            ],
        ]);
    }

    public function destroy(
        HomeBanner $homeBanner
    ): JsonResponse {
        Gate::authorize('home_banners.delete');

        $imagePath = $homeBanner->image_path;

        DB::transaction(
            fn () => $homeBanner->delete()
        );

        $this->imageService->delete(
            $imagePath
        );

        return response()->json([
            'success' => true,

            'message' => 'Home banner deleted successfully.',
        ]);
    }

    private function loadBanner(
        HomeBanner $banner
    ): HomeBanner {
        return $banner->load([
            'product:id,name,slug,is_active',
        ]);
    }
}