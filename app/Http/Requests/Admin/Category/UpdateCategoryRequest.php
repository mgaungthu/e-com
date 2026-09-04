<?php

namespace App\Http\Requests\Admin\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

            /*
             * Controller ရဲ့ generateUniqueSlug()
             * က duplicate slug ကို auto suffix
             * ထည့်ပေးမှာဖြစ်တဲ့အတွက် unique rule
             * မလိုပါ။
             */
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Category|null $category */
                $category = $this->route('category');

                if (! $category) {
                    return;
                }

                $parentId = $this->input('parent_id');

                if ($parentId === null) {
                    return;
                }

                $parentId = (int) $parentId;

                /*
                 * Self parent protection
                 * Rule::notIn() ကလည်းစစ်ထားပေမယ့်
                 * defensive check ထပ်ထားပါတယ်။
                 */
                if ($parentId === (int) $category->id) {
                    $validator->errors()->add(
                        'parent_id',
                        'A category cannot be its own parent.',
                    );

                    return;
                }

                /*
                 * Descendant protection
                 *
                 * Selected parent က current category ရဲ့
                 * descendant ဖြစ်နေရင် hierarchy cycle
                 * ဖြစ်နိုင်ပါတယ်။
                 */
                if (
                    $this->isDescendant(
                        $category,
                        $parentId,
                    )
                ) {
                    $validator->errors()->add(
                        'parent_id',
                        'A child category cannot be selected as the parent.',
                    );
                }
            },
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

            'remove_image' => $this->boolean(
                'remove_image',
            ),

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

            'parent_id.not_in' =>
                'A category cannot be its own parent.',

            'image.image' =>
                'The uploaded file must be an image.',

            'image.mimes' =>
                'The image must be a JPG, JPEG, PNG, or WebP file.',

            'image.max' =>
                'The image must not be larger than 10 MB.',
        ];
    }

    private function isDescendant(
        Category $category,
        int $parentId,
    ): bool {
        $current = Category::query()
            ->select([
                'id',
                'parent_id',
            ])
            ->find($parentId);

        while ($current !== null) {
            if (
                (int) $current->id ===
                (int) $category->id
            ) {
                return true;
            }

            if ($current->parent_id === null) {
                break;
            }

            $current = Category::query()
                ->select([
                    'id',
                    'parent_id',
                ])
                ->find($current->parent_id);
        }

        return false;
    }
}