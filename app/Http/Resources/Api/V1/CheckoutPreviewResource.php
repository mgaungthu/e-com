<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutPreviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cart' => $this['cart'],
            'shipping_address' => $this['shipping_address'],
            'billing_address' => $this['billing_address'],
            'payment_method' => $this['payment_method'],

            'summary' => [
                'subtotal' => $this['subtotal'],
                'discount_total' => $this['discount_total'],
                'shipping_total' => $this['shipping_total'],
                'tax_rate' => $this['tax_rate'],
                'tax_total' => $this['tax_total'],
                'grand_total' => $this['grand_total'],
            ],
        ];
    }
}
