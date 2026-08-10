<?php

namespace App\Http\Requests\Api\V1\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => [
                'required',
                'integer',
                'exists:addresses,id',
            ],

            'billing_address_id' => [
                'required',
                'integer',
                'exists:addresses,id',
            ],

            'payment_method_id' => [
                'required',
                'integer',
                'exists:payment_methods,id',
            ],
        ];
    }
}
