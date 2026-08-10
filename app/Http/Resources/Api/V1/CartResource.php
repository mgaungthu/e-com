<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items');

        $subtotal = $this->items->sum(function ($item) {
            return (float) $item->unit_price * $item->quantity;
        });

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,

            'items' => CartItemResource::collection($items),

            'summary' => [
                'total_items' => (int) $this->items->sum('quantity'),
                'unique_items' => $this->items->count(),
                'subtotal' => round($subtotal, 2),
                'total' => round($subtotal, 2),
            ],

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
