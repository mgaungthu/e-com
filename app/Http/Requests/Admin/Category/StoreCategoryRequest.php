<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('categories.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNull('deleted_at'),
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
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
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
            'parent_id' => $this->filled('parent_id')
                ? $this->input('parent_id')
                : null,

            'slug' => $this->filled('slug')
                ? $this->input('slug')
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

            'is_active' => $this->boolean('is_active'),

            'sort_order' => $this->input(
                'sort_order',
                0,
            ),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'The category name field is required.',

            'parent_id.exists' =>
                'The selected parent category is invalid.',

            'image.image' =>
                'The uploaded file must be an image.',

            'image.mimes' =>
                'The image must be a JPG, JPEG, PNG, or WebP file.',

            'image.max' =>
                'The image must not be larger than 10 MB.',
        ];
    }
}