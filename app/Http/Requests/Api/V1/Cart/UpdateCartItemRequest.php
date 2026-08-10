<?php

namespace App\Http\Requests\Api\V1\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:999',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'The quantity is required.',
            'quantity.min' => 'The quantity must be at least 1.',
        ];
    }
}
