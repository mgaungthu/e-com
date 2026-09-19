<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Resolve Payment Method
        |--------------------------------------------------------------------------
        */

        $paymentMethod = null;

        $paymentMethodId =
            $this->integer('payment_method_id');

        if ($paymentMethodId > 0) {
            $paymentMethod = PaymentMethod::query()
                ->whereKey($paymentMethodId)
                ->where('is_active', true)
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Proof Requirement
        |--------------------------------------------------------------------------
        |
        | Payment proof requirement comes directly from the selected
        | payment method configuration.
        |
        | COD:
        |   requires_proof = false
        |
        | E-Wallet:
        |   requires_proof = true
        |--------------------------------------------------------------------------
        */

        $requiresPaymentProof =
            (bool) ($paymentMethod?->requires_proof ?? false);

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
                Rule::exists('payment_methods', 'id')
                    ->where('is_active', true),
            ],

            'payment_reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'payment_proof' => [
                Rule::requiredIf(
                    $requiresPaymentProof,
                ),
                'nullable',
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
            'shipping_address_id.required' =>
                'The shipping address is required.',

            'shipping_address_id.exists' =>
                'The selected shipping address does not exist.',

            'billing_address_id.required' =>
                'The billing address is required.',

            'billing_address_id.exists' =>
                'The selected billing address does not exist.',

            'payment_method_id.required' =>
                'The payment method is required.',

            'payment_method_id.exists' =>
                'The selected payment method is unavailable.',

            'payment_proof.required' =>
                'The payment proof screenshot is required.',

            'payment_proof.image' =>
                'The payment proof must be an image.',

            'payment_proof.mimes' =>
                'The payment proof must be a JPG, JPEG, PNG, or WEBP image.',

            'payment_proof.max' =>
                'The payment proof may not be larger than 5 MB.',
        ];
    }
}