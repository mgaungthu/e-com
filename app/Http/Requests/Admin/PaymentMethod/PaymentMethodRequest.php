<?php

namespace App\Http\Requests\Admin\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class PaymentMethodRequest extends FormRequest
{
    protected function commonRules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'type' => [
                'required',
                'string',
                Rule::in([
                    'cod',
                    'ewallet',
                ]),
            ],

            'account_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'account_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'instructions' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'qr_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'remove_logo' => [
                'sometimes',
                'boolean',
            ],

            'remove_qr_image' => [
                'sometimes',
                'boolean',
            ],

            'requires_proof' => [
                'required',
                'boolean',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'The payment method name is required.',

            'name.max' =>
                'The payment method name may not be greater than 100 characters.',

            'type.required' =>
                'The payment method type is required.',

            'type.in' =>
                'The selected payment method type is invalid.',

            'account_name.max' =>
                'The account name may not be greater than 150 characters.',

            'account_number.max' =>
                'The account number may not be greater than 100 characters.',

            'instructions.max' =>
                'The instructions may not be greater than 2000 characters.',

            'logo.image' =>
                'The logo must be an image.',

            'logo.mimes' =>
                'The logo must be a JPG, JPEG, PNG, or WEBP image.',

            'logo.max' =>
                'The logo may not be larger than 5 MB.',

            'qr_image.image' =>
                'The QR image must be an image.',

            'qr_image.mimes' =>
                'The QR image must be a JPG, JPEG, PNG, or WEBP image.',

            'qr_image.max' =>
                'The QR image may not be larger than 5 MB.',

            'requires_proof.required' =>
                'Please specify whether payment proof is required.',

            'requires_proof.boolean' =>
                'The payment proof setting must be true or false.',

            'is_active.required' =>
                'Please specify whether the payment method is active.',

            'is_active.boolean' =>
                'The active setting must be true or false.',

            'sort_order.required' =>
                'The sort order is required.',

            'sort_order.integer' =>
                'The sort order must be a whole number.',

            'sort_order.min' =>
                'The sort order must be zero or greater.',
        ];
    }
}