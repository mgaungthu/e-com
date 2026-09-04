<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.create') ?? false;
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $primaryNewImageIndex = $this->input(
                    'primary_new_image_index'
                );

                $newImageCount = count(
                    $this->file('images', [])
                );

                if ($primaryNewImageIndex === null) {
                    return;
                }

                $index = (int) $primaryNewImageIndex;

                if (
                    $index < 0 ||
                    $index >= $newImageCount
                ) {
                    $validator->errors()->add(
                        'primary_new_image_index',
                        'The selected primary image is invalid.'
                    );
                }
            },
        ];
    }
}