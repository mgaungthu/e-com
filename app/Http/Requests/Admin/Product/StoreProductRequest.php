<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Validation\Rule;

class StoreProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return true;

        // return $this->user()?->can('products.create') ?? false;
    }

    public function rules(): array
    {
        return [
            ...$this->commonRules(),

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug'),
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku'),
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode'),
            ],
        ];
    }
}