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
                        'image',
                        'feed',
                        'product',
                    ]),
                ],

                'message' => [
                    'nullable',
                    'string',
                    'max:5000',
                    'required_if:type,text',
                ],

                'image' => [
                    'nullable',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:8192',
                    'required_if:type,image',
                ],

                'feed_id' => [
                    'nullable',
                    'integer',
                    'exists:feeds,id',
                    'required_if:type,feed',
                ],

                'product_id' => [
                    'nullable',
                    'integer',
                    'exists:products,id',
                    'required_if:type,product',
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