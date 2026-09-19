<?php

namespace App\Http\Requests\Admin\Location;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'locations.create',
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists(
                    'locations',
                    'id',
                ),
            ],

            'name_en' => [
                'required',
                'string',
                'max:150',
            ],

            'name_mm' => [
                'nullable',
                'string',
                'max:150',
            ],

            'type' => [
                'required',
                'string',
                'max:30',
            ],

            'shipping_fee' => [
                'nullable',
                'numeric',
                'min:0',
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
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'parent_id' => $this->filled(
                'parent_id',
            )
                ? $this->input('parent_id')
                : null,

            'name_mm' => $this->filled(
                'name_mm',
            )
                ? $this->input('name_mm')
                : null,

            'shipping_fee' => $this->filled(
                'shipping_fee',
            )
                ? $this->input('shipping_fee')
                : null,

            'is_active' => $this->boolean(
                'is_active',
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
            'parent_id.exists' =>
                'The selected parent location is invalid.',

            'name_en.required' =>
                'The English location name field is required.',

            'type.required' =>
                'The location type field is required.',

            'shipping_fee.numeric' =>
                'The shipping fee must be a valid number.',

            'shipping_fee.min' =>
                'The shipping fee cannot be negative.',
        ];
    }
}