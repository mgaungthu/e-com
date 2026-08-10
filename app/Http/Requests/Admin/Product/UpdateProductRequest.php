<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.update') ?? false;
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $product = $this->route('product');
                $ownedIds = $product->images()->pluck('id');
                $removedIds = collect($this->input('removed_image_ids', []));
                $orderedIds = collect($this->input('image_order', []));
                $primaryId = $this->input('primary_image_id');
                $primaryNewImageIndex = $this->input('primary_new_image_index');

                if ($removedIds->diff($ownedIds)->isNotEmpty()) {
                    $validator->errors()->add('removed_image_ids', 'One or more selected images do not belong to this product.');
                }

                if ($orderedIds->diff($ownedIds)->isNotEmpty()) {
                    $validator->errors()->add('image_order', 'The image order contains an invalid image.');
                }

                if ($primaryId !== null && ! $ownedIds->contains((int) $primaryId)) {
                    $validator->errors()->add('primary_image_id', 'The selected primary image is invalid.');
                }

                if ($primaryId !== null && $removedIds->contains((int) $primaryId)) {
                    $validator->errors()->add('primary_image_id', 'The primary image cannot also be removed.');
                }

                $remainingCount = $ownedIds->diff($removedIds)->count();
                $newCount = count($this->file('images', []));

                if (
                    $primaryNewImageIndex !== null &&
                    (int) $primaryNewImageIndex >= $newCount
                ) {
                    $validator->errors()->add('primary_new_image_index', 'The selected primary image is invalid.');
                }

                if ($remainingCount + $newCount > 10) {
                    $validator->errors()->add('images', 'A product can have at most 10 images.');
                }
            },
        ];
    }
}
