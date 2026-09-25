<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LocationResource;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                'exists:locations,id',
            ],
        ]);

        $parentId = $validated['parent_id'] ?? null;

        $locations = Location::query()
            ->where('is_active', true)
            ->when($parentId !== null, fn ($query) => $query->where('parent_id', $parentId), fn ($query) => $query->whereNull('parent_id'))
            ->withCount([
                'children' => fn ($query) => $query->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'locations' => LocationResource::collection($locations),
            ],
        ]);
    }

    public function deliveryAreas(): JsonResponse
    {
        $locations = Location::query()
            ->where('is_active', true)
            ->where('type', 'township')
            ->with([
                'parent.parent',
            ])
            ->orderBy('sort_order')
            ->orderBy('name_en')
            ->get();

        $deliveryAreas = $locations
            ->map(function (Location $location): array {
                $region = $this->resolveRegion($location);

                return [
                    'id' => $location->id,
                    'parent_id' => $location->parent_id,
                    'name_en' => $location->name_en,
                    'name_mm' => $location->name_mm,
                    'type' => $location->type,

                    'region_name_en' => $region?->name_en,
                    'region_name_mm' => $region?->name_mm,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'locations' => $deliveryAreas,
            ],
        ]);
    }

    private function resolveRegion(Location $location): ?Location
    {
        $current = $location->parent;

        while ($current) {
            if (
                in_array($current->type, [
                    'region',
                    'state',
                ], true, )
            ) {
                return $current;
            }

            $current = $current->parent;
        }

        return null;
    }
}
