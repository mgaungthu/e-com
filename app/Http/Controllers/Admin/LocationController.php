<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Location\StoreLocationRequest;
use App\Http\Requests\Admin\Location\UpdateLocationRequest;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LocationController extends Controller
{
    public function index(
        Request $request,
    ): JsonResponse {
        Gate::authorize(
            'locations.view',
        );

        $query = Location::query()
            ->with([
                'parent:id,name_en,name_mm,type',
            ])
            ->withCount('children');

        $search = trim(
            (string) $request->input(
                'search',
            ),
        );

        if ($search !== '') {
            $query->where(
                function ($query) use ($search) {
                    $query
                        ->where(
                            'name_en',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhere(
                            'name_mm',
                            'like',
                            "%{$search}%",
                        );
                },
            );
        }

        if (
            $request->filled(
                'parent_id',
            )
        ) {
            $query->where(
                'parent_id',
                $request->integer(
                    'parent_id',
                ),
            );
        }

        if (
            $request->filled('type')
        ) {
            $query->where(
                'type',
                (string) $request->input(
                    'type',
                ),
            );
        }

        if (
            $request->filled(
                'status',
            )
        ) {
            $status = (string)
                $request->input(
                    'status',
                );

            if ($status === 'active') {
                $query->where(
                    'is_active',
                    true,
                );
            }

            if (
                $status === 'inactive'
            ) {
                $query->where(
                    'is_active',
                    false,
                );
            }
        }

        $perPage = min(
            max(
                (int) $request->input(
                    'per_page',
                    15,
                ),
                1,
            ),
            100,
        );

        $locations = $query
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success' => true,

            'data' => $locations,
        ]);
    }

    public function store(
        StoreLocationRequest $request,
    ): JsonResponse {
        $validated =
            $request->validated();

        $location =
            Location::query()->create([
                'parent_id' =>
                    $validated[
                        'parent_id'
                    ] ?? null,

                'name_en' =>
                    $validated[
                        'name_en'
                    ],

                'name_mm' =>
                    $validated[
                        'name_mm'
                    ] ?? null,

                'type' =>
                    $validated[
                        'type'
                    ],

                'shipping_fee' =>
                    $validated[
                        'shipping_fee'
                    ] ?? null,

                'is_active' =>
                    (bool) $validated[
                        'is_active'
                    ],

                'sort_order' =>
                    (int) $validated[
                        'sort_order'
                    ],
            ]);

        $location->load(
            'parent:id,name_en,name_mm,type',
        );

        $location->loadCount(
            'children',
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Location created successfully.',

            'data' => [
                'location' =>
                    $this->locationData(
                        $location,
                    ),
            ],
        ], 201);
    }

    public function show(
        Location $location,
    ): JsonResponse {
        Gate::authorize(
            'locations.view',
        );

        $location->load(
            'parent:id,name_en,name_mm,type',
        );

        $location->loadCount(
            'children',
        );

        return response()->json([
            'success' => true,

            'data' => [
                'location' =>
                    $this->locationData(
                        $location,
                    ),
            ],
        ]);
    }

    public function update(
        UpdateLocationRequest $request,
        Location $location,
    ): JsonResponse {
        $validated =
            $request->validated();

        $location->update([
            'parent_id' =>
                $validated[
                    'parent_id'
                ] ?? null,

            'name_en' =>
                $validated[
                    'name_en'
                ],

            'name_mm' =>
                $validated[
                    'name_mm'
                ] ?? null,

            'type' =>
                $validated[
                    'type'
                ],

            'shipping_fee' =>
                $validated[
                    'shipping_fee'
                ] ?? null,

            'is_active' =>
                (bool) $validated[
                    'is_active'
                ],

            'sort_order' =>
                (int) $validated[
                    'sort_order'
                ],
        ]);

        $updatedLocation =
            $location
                ->fresh()
                ->load(
                    'parent:id,name_en,name_mm,type',
                );

        $updatedLocation->loadCount(
            'children',
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Location updated successfully.',

            'data' => [
                'location' =>
                    $this->locationData(
                        $updatedLocation,
                    ),
            ],
        ]);
    }

    private function locationData(
        Location $location,
    ): array {
        return [
            'id' =>
                $location->id,

            'parent_id' =>
                $location->parent_id,

            'parent' =>
                $location->parent
                    ? [
                        'id' =>
                            $location
                                ->parent
                                ->id,

                        'name_en' =>
                            $location
                                ->parent
                                ->name_en,

                        'name_mm' =>
                            $location
                                ->parent
                                ->name_mm,

                        'type' =>
                            $location
                                ->parent
                                ->type,
                    ]
                    : null,

            'name_en' =>
                $location->name_en,

            'name_mm' =>
                $location->name_mm,

            'type' =>
                $location->type,

            'shipping_fee' =>
                $location->shipping_fee !== null
                    ? (float) $location
                        ->shipping_fee
                    : null,

            'is_active' =>
                (bool) $location
                    ->is_active,

            'sort_order' =>
                (int) $location
                    ->sort_order,

            'children_count' =>
                isset(
                    $location
                        ->children_count,
                )
                    ? (int) $location
                        ->children_count
                    : 0,

            'created_at' =>
                $location
                    ->created_at
                    ?->toISOString(),

            'updated_at' =>
                $location
                    ->updated_at
                    ?->toISOString(),
        ];
    }
}