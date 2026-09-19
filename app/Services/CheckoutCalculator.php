<?php

namespace App\Services;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Location;
use App\Models\Setting;
use Illuminate\Support\Collection;

class CheckoutCalculator
{
    /**
     * @param  Collection<int, CartItem>  $items
     * @return array{subtotal: float, discount_total: float, shipping_total: float, tax_rate: float, tax_total: float, grand_total: float, lines: array<int, array{cart_item: CartItem, unit_price: float, line_total: float}>}
     */
    public function calculate(
        Collection $items,
        ?Address $shippingAddress = null,
    ): array {
        $subtotal = 0.0;
        $lines = [];

        foreach ($items as $item) {
            $unitPrice = $this->resolveProductPrice($item->product);
            $lineTotal = round($unitPrice * $item->quantity, 2);

            $subtotal += $lineTotal;

            $lines[] = [
                'cart_item' => $item,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        $subtotal = round($subtotal, 2);
        $discountTotal = 0.0;

        $shippingTotal = $this->resolveShippingFee(
            $shippingAddress,
        );

        $taxRate = $this->settingDecimal('tax_rate');

        $taxTotal = round(
            max(0, $subtotal - $discountTotal)
                * ($taxRate / 100),
            2,
        );

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'shipping_total' => $shippingTotal,
            'tax_rate' => $taxRate,
            'tax_total' => $taxTotal,

            'grand_total' => round(
                $subtotal
                    - $discountTotal
                    + $shippingTotal
                    + $taxTotal,
                2,
            ),

            'lines' => $lines,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Shipping Fee
    |--------------------------------------------------------------------------
    |
    | Start from the most specific location selected on the shipping address.
    |
    | Example:
    |
    | Ward 1              -> null
    | South Okkalapa      -> 2500
    | Yangon              -> null
    | Yangon Region       -> 3000
    |
    | Ward 1 therefore resolves to 2500.
    |
    | Addresses without location_id continue to use the existing global
    | shipping_fee setting for backward compatibility.
    |--------------------------------------------------------------------------
    */

    private function resolveShippingFee(
        ?Address $shippingAddress,
    ): float {
        if (
            ! $shippingAddress
            || ! $shippingAddress->location_id
        ) {
            return $this->settingDecimal(
                'shipping_fee',
            );
        }

        $location = Location::query()
            ->find(
                $shippingAddress->location_id,
            );

        while ($location) {
            if (
                $location->shipping_fee !== null
            ) {
                return round(
                    (float) $location->shipping_fee,
                    2,
                );
            }

            if (
                $location->parent_id === null
            ) {
                break;
            }

            $location = Location::query()
                ->find(
                    $location->parent_id,
                );
        }

        return $this->settingDecimal(
            'shipping_fee',
        );
    }

    private function resolveProductPrice(
        $product,
    ): float {
        $price = (float) $product->price;

        $salePrice =
            $product->sale_price !== null
                ? (float) $product->sale_price
                : null;

        return $salePrice !== null
            && $salePrice >= 0
            && $salePrice < $price
                ? $salePrice
                : $price;
    }

    private function settingDecimal(
        string $key,
    ): float {
        $value = Setting::query()
            ->where('key', $key)
            ->value('value');

        return $value !== null
            && $value !== ''
            && is_numeric($value)
                ? round(
                    (float) $value,
                    2,
                )
                : 0.0;
    }
}