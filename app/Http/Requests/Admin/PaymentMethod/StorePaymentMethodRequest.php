<?php

namespace App\Http\Requests\Admin\PaymentMethod;

use Illuminate\Validation\Rule;

class StorePaymentMethodRequest extends PaymentMethodRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'payment_methods.create'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            ...$this->commonRules(),

            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique(
                    'payment_methods',
                    'code'
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),

            'code.required' =>
                'The payment method code is required.',

            'code.max' =>
                'The payment method code may not be greater than 50 characters.',

            'code.regex' =>
                'The payment method code may only contain lowercase letters, numbers, hyphens, and underscores.',

            'code.unique' =>
                'The payment method code has already been taken.',
        ];
    }
}