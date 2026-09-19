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
            ->when(
                $parentId !== null,
                fn ($query) => $query->where('parent_id', $parentId),
                fn ($query) => $query->whereNull('parent_id'),
            )
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
}