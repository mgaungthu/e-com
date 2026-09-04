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

                if (! $product) {
                    return;
                }

                $ownedIds = $product
                    ->images()
                    ->pluck('id')
                    ->map(fn ($id): int => (int) $id);

                $removedIds = collect(
                    $this->input('removed_image_ids', [])
                )
                    ->map(fn ($id): int => (int) $id);

                $orderedIds = collect(
                    $this->input('image_order', [])
                )
                    ->map(fn ($id): int => (int) $id);

                $primaryId = $this->input(
                    'primary_image_id'
                );

                $primaryNewImageIndex = $this->input(
                    'primary_new_image_index'
                );

                /*
                |--------------------------------------------------------------------------
                | Removed images
                |--------------------------------------------------------------------------
                */

                if (
                    $removedIds
                        ->diff($ownedIds)
                        ->isNotEmpty()
                ) {
                    $validator->errors()->add(
                        'removed_image_ids',
                        'One or more selected images do not belong to this product.'
                    );
                }

                if (
                    $removedIds->unique()->count() !==
                    $removedIds->count()
                ) {
                    $validator->errors()->add(
                        'removed_image_ids',
                        'The removed image list contains duplicate images.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Image ordering
                |--------------------------------------------------------------------------
                */

                if (
                    $orderedIds
                        ->diff($ownedIds)
                        ->isNotEmpty()
                ) {
                    $validator->errors()->add(
                        'image_order',
                        'The image order contains an invalid image.'
                    );
                }

                if (
                    $orderedIds->unique()->count() !==
                    $orderedIds->count()
                ) {
                    $validator->errors()->add(
                        'image_order',
                        'The image order contains duplicate images.'
                    );
                }

                if (
                    $orderedIds
                        ->intersect($removedIds)
                        ->isNotEmpty()
                ) {
                    $validator->errors()->add(
                        'image_order',
                        'Removed images cannot be included in the image order.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Existing primary image
                |--------------------------------------------------------------------------
                */

                if ($primaryId !== null) {
                    $primaryId = (int) $primaryId;

                    if (! $ownedIds->contains($primaryId)) {
                        $validator->errors()->add(
                            'primary_image_id',
                            'The selected primary image is invalid.'
                        );
                    }

                    if ($removedIds->contains($primaryId)) {
                        $validator->errors()->add(
                            'primary_image_id',
                            'The primary image cannot also be removed.'
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | New primary image
                |--------------------------------------------------------------------------
                */

                $newImageCount = count(
                    $this->file('images', [])
                );

                if ($primaryNewImageIndex !== null) {
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
                }

                /*
                |--------------------------------------------------------------------------
                | Only one primary source
                |--------------------------------------------------------------------------
                */

                if (
                    $primaryId !== null &&
                    $primaryNewImageIndex !== null
                ) {
                    $validator->errors()->add(
                        'primary_new_image_index',
                        'Select either an existing image or a new image as primary, not both.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Maximum images
                |--------------------------------------------------------------------------
                */

                $remainingCount = $ownedIds
                    ->diff($removedIds)
                    ->count();

                if (
                    $remainingCount + $newImageCount > 10
                ) {
                    $validator->errors()->add(
                        'images',
                        'A product can have at most 10 images.'
                    );
                }
            },
        ];
    }
}