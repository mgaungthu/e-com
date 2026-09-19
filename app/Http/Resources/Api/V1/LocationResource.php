<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name_en' => $this->name_en,
            'name_mm' => $this->name_mm,
            'type' => $this->type,
            'has_children' => isset($this->children_count)
                ? $this->children_count > 0
                : false,
        ];
    }
}