<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'categories.update',
        ) ?? false;
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at'),
                Rule::notIn([
                    $category?->id,
                ]),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')
                    ->ignore($category),
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'remove_image' => [
                'nullable',
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean(
                'remove_image',
            ),
            'sort_order' => $this->input(
                'sort_order',
                0,
            ),
        ]);
    }
}
