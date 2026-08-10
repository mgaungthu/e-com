<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => (int) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'line_total' => round((float) $this->unit_price * $this->quantity, 2),

            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                    'image_url' => $this->product->image_url,
                    'price' => (float) $this->product->price,
                    'sale_price' => $this->product->sale_price !== null
                        ? (float) $this->product->sale_price
                        : null,
                    'stock_quantity' => (int) $this->product->stock_quantity,
                    'is_active' => (bool) $this->product->is_active,
                ];
            }),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
