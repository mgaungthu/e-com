<?php

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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

            'payment_reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'payment_proof' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address_id.required' => 'The shipping address is required.',
            'shipping_address_id.exists' => 'The selected shipping address does not exist.',

            'billing_address_id.required' => 'The billing address is required.',
            'billing_address_id.exists' => 'The selected billing address does not exist.',

            'payment_method_id.required' => 'The payment method is required.',
            'payment_method_id.exists' => 'The selected payment method does not exist.',

            'payment_proof.required' => 'The payment proof screenshot is required.',
            'payment_proof.image' => 'The payment proof must be an image.',
            'payment_proof.mimes' => 'The payment proof must be a JPG, JPEG, PNG, or WEBP image.',
            'payment_proof.max' => 'The payment proof may not be larger than 5 MB.',
        ];
    }
}
