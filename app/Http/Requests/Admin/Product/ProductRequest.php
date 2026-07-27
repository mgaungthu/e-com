<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'category_id' => $this->filled('category_id')
                ? $this->input('category_id')
                : null,

            'slug' => $this->filled('slug')
                ? $this->input('slug')
                : null,

            'barcode' => $this->filled('barcode')
                ? $this->input('barcode')
                : null,

            'sale_price' => $this->filled('sale_price')
                ? $this->input('sale_price')
                : null,

            'short_description' => $this->filled('short_description')
                ? $this->input('short_description')
                : null,

            'description' => $this->filled('description')
                ? $this->input('description')
                : null,

            'seo_title' => $this->filled('seo_title')
                ? $this->input('seo_title')
                : null,

            'seo_description' => $this->filled('seo_description')
                ? $this->input('seo_description')
                : null,

            'remove_image' => $this->boolean('remove_image'),
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }

    protected function commonRules(): array
    {
        return [
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'short_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'sale_price' => [
                'nullable',
                'numeric',
                'min:0',
                'lt:price',
            ],

            'stock_quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'low_stock_threshold' => [
                'required',
                'integer',
                'min:0',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'remove_image' => [
                'required',
                'boolean',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'is_featured' => [
                'required',
                'boolean',
            ],

            'seo_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'seo_description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'The product name field is required.',

            'sku.required' =>
                'The SKU field is required.',

            'sku.unique' =>
                'This SKU is already in use.',

            'barcode.unique' =>
                'This barcode is already in use.',

            'price.required' =>
                'The regular price field is required.',

            'sale_price.lt' =>
                'The sale price must be less than the regular price.',

            'stock_quantity.required' =>
                'The stock quantity field is required.',

            'low_stock_threshold.required' =>
                'The low stock threshold field is required.',

            'image.image' =>
                'The uploaded file must be an image.',

            'image.mimes' =>
                'The image must be a JPG, JPEG, PNG, or WebP file.',

            'image.max' =>
                'The image must not be larger than 5 MB.',
        ];
    }

    protected function failedValidation(
        Validator $validator,
    ): void {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422),
        );
    }
}