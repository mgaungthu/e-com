<?php

namespace App\Http\Requests\Api\V1\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in([
                    'text',
                    'feed',
                    'product',
                ]),
            ],

            'message' => [
                Rule::requiredIf(
                    fn () => $this->input('type') === 'text',
                ),
                'nullable',
                'string',
                'max:5000',
            ],

            'feed_id' => [
                Rule::requiredIf(
                    fn () => $this->input('type') === 'feed',
                ),
                'nullable',
                'integer',
                'exists:feeds,id',
            ],

            'product_id' => [
                Rule::requiredIf(
                    fn () => $this->input('type') === 'product',
                ),
                'nullable',
                'integer',
                'exists:products,id',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('message')) {
            $this->merge([
                'message' => is_string($this->input('message'))
                    ? trim($this->input('message'))
                    : $this->input('message'),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'type.required' =>
                'Message type is required.',

            'type.in' =>
                'Message type must be text, feed, or product.',

            'message.required' =>
                'Message text is required.',

            'message.max' =>
                'Message may not be greater than 5000 characters.',

            'feed_id.required' =>
                'A feed post is required for feed messages.',

            'feed_id.exists' =>
                'The selected feed post does not exist.',

            'product_id.required' =>
                'A product is required for product messages.',

            'product_id.exists' =>
                'The selected product does not exist.',
        ];
    }
}