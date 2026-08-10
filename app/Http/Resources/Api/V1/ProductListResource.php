<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = (float) $this->price;
        $salePrice = $this->sale_price !== null
            ? (float) $this->sale_price
            : null;

        $hasDiscount = $salePrice !== null && $salePrice < $price;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,

            'short_description' => $this->short_description,

            'price' => $price,
            'sale_price' => $salePrice,
            'final_price' => $hasDiscount ? $salePrice : $price,

            'discount_percentage' => $hasDiscount && $price > 0
                ? (int) round((($price - $salePrice) / $price) * 100)
                : 0,

            'stock_quantity' => (int) $this->stock_quantity,
            'low_stock_threshold' => (int) $this->low_stock_threshold,

            'is_in_stock' => $this->stock_quantity > 0,
            'is_low_stock' => $this->stock_quantity > 0
                && $this->stock_quantity <= $this->low_stock_threshold,

            'is_featured' => (bool) $this->is_featured,

            'image_url' => $this->image_url
                ?? ($this->image_path
                    ? asset('storage/'.$this->image_path)
                    : null),

            'primary_image' => $this->whenLoaded('primaryImage', fn () => $this->primaryImage
                    ? new ProductImageResource($this->primaryImage)
                    : null),

            'category' => $this->whenLoaded('category', function () {
                if (! $this->category) {
                    return null;
                }

                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
