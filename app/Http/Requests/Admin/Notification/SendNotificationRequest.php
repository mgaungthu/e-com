<?php

namespace App\Http\Requests\Admin\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'product_id' => $this->filled('product_id')
                ? $this->input('product_id')
                : null,

            'title' => trim(
                (string) $this->input('title', '')
            ),

            'body' => trim(
                (string) $this->input('body', '')
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in([
                    'new_product',
                    'product_restocked',
                    'general',
                ]),
            ],

            'product_id' => [
                Rule::requiredIf(
                    fn (): bool =>
                        in_array(
                            $this->input('type'),
                            [
                                'new_product',
                                'product_restocked',
                            ],
                            true,
                        )
                ),
                'nullable',
                'integer',
                'exists:products,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'body' => [
                'required',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' =>
                'The notification type is required.',

            'type.in' =>
                'The selected notification type is invalid.',

            'product_id.required' =>
                'Please select a product for this notification.',

            'product_id.exists' =>
                'The selected product does not exist.',

            'title.required' =>
                'The notification title is required.',

            'body.required' =>
                'The notification message is required.',
        ];
    }
}