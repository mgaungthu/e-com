<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Validation\Rule;

class UpdateProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return true;

        // return $this->user()?->can('products.update') ?? false;
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            ...$this->commonRules(),

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')
                    ->ignore($product?->id),
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->ignore($product?->id),
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')
                    ->ignore($product?->id),
            ],
        ];
    }
}