<?php

namespace App\Http\Requests\Admin\Inventory;

use App\Enums\InventoryTransactionType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

abstract class InventoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'quantity' => $this->filled('quantity')
                ? $this->integer('quantity')
                : null,

            'reason' => $this->filled('reason')
                ? trim((string) $this->input('reason'))
                : null,

            'note' => $this->filled('note')
                ? trim((string) $this->input('note'))
                : null,
        ]);
    }

    protected function commonRules(): array
    {
        return [
            'type' => [
                'required',
                Rule::enum(InventoryTransactionType::class),
            ],

            'quantity' => [
                'required',
                'integer',
                $this->input('type') === 'set'
                    ? 'min:0'
                    : 'min:1',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:255',
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please select an adjustment type.',

            'type.enum' => 'The selected adjustment type is invalid.',

            'quantity.required' => 'The quantity field is required.',

            'quantity.integer' => 'The quantity must be a whole number.',

            'quantity.min' => 'The quantity must be at least 1.',

            'reason.max' => 'The reason must not exceed 255 characters.',

            'note.max' => 'The note must not exceed 1000 characters.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422), );
    }
}
