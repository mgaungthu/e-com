<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,

            'image_url' => $this->image_url
                ?? ($this->image_path
                    ? asset('storage/'.$this->image_path)
                    : null),

            'sort_order' => (int) $this->sort_order,

            'products_count' => $this->whenCounted('products'),

            'parent' => $this->whenLoaded('parent', function () {
                if (! $this->parent) {
                    return null;
                }

                return [
                    'id' => $this->parent->id,
                    'name' => $this->parent->name,
                    'slug' => $this->parent->slug,
                ];
            }),

            'children' => CategoryResource::collection($this->whenLoaded('children')),

            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ],

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
