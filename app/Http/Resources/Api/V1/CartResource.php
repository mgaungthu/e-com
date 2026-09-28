<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items');

        /*
        |--------------------------------------------------------------------------
        | Subtotal
        |--------------------------------------------------------------------------
        */

        $subtotal = $this->items->sum(function ($item) {
            return (float) $item->unit_price * $item->quantity;
        });

        $subtotal = round($subtotal, 2);

        /*
        |--------------------------------------------------------------------------
        | Member Discount
        |--------------------------------------------------------------------------
        |
        | Keep the API field names aligned with checkout:
        |
        | discount_percentage
        | discount_total
        |
        | "Member Discount" is a customer-facing UI label only.
        |
        | The same admin-controlled settings used by CheckoutCalculator are
        | respected here so the cart can show the discount before checkout.
        |--------------------------------------------------------------------------
        */

        $discountEnabled = $this->settingBoolean(
            'checkout_discount_enabled',
        );

        $discountPercentage = $discountEnabled
            ? min(
                100,
                max(
                    0,
                    $this->settingDecimal(
                        'checkout_discount_percent',
                    ),
                ),
            )
            : 0.0;

        $discountTotal = round(
            $subtotal * ($discountPercentage / 100),
            2,
        );

        /*
        |--------------------------------------------------------------------------
        | Cart Total
        |--------------------------------------------------------------------------
        |
        | Cart total does not include shipping or tax.
        |
        | Shipping depends on the delivery address and is calculated during
        | checkout.
        |--------------------------------------------------------------------------
        */

        $total = round(
            max(0, $subtotal - $discountTotal),
            2,
        );

        return [
            'id' => $this->id,

            'user_id' => $this->user_id,

            'items' => CartItemResource::collection($items),

            'summary' => [
                'total_items' => (int) $this->items->sum('quantity'),

                'unique_items' => $this->items->count(),

                'subtotal' => $subtotal,

                'discount_percentage' => $discountPercentage,

                'discount_total' => $discountTotal,

                'total' => $total,
            ],

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Setting Helpers
    |--------------------------------------------------------------------------
    */

    private function settingDecimal(string $key): float
    {
        $value = Setting::query()
            ->where('key', $key)
            ->value('value');

        return $value !== null
            && $value !== ''
            && is_numeric($value)
                ? round((float) $value, 2)
                : 0.0;
    }

    private function settingBoolean(string $key): bool
    {
        $value = Setting::query()
            ->where('key', $key)
            ->value('value');

        return filter_var(
            $value,
            FILTER_VALIDATE_BOOL,
        );
    }
}