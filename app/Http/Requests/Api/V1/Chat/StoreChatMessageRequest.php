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
            /*
            |--------------------------------------------------------------------------
            | Message Type
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Text / Image Caption
            |--------------------------------------------------------------------------
            |
            | Required for text messages.
            | Optional for image messages and acts as the image caption.
            |
            */

            'message' => [
                'nullable',
                'string',
                'max:5000',
                'required_if:type,text',
            ],

            /*
            |--------------------------------------------------------------------------
            | Image
            |--------------------------------------------------------------------------
            */

            'image' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
                'required_if:type,image',
            ],

            /*
            |--------------------------------------------------------------------------
            | Feed
            |--------------------------------------------------------------------------
            */

            'feed_id' => [
                'nullable',
                'integer',
                'exists:feeds,id',
                'required_if:type,feed',
            ],

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

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
            $message =
                $this->input('message');

            $this->merge([
                'message' => is_string($message)
                        ? trim($message)
                        : $message,
            ]);
        }
    }

    public function messages(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Type
            |--------------------------------------------------------------------------
            */

            'type.required' => 'Message type is required.',

            'type.in' => 'Message type must be text, image, feed, or product.',

            /*
            |--------------------------------------------------------------------------
            | Message
            |--------------------------------------------------------------------------
            */

            'message.required_if' => 'Message text is required for text messages.',

            'message.string' => 'Message must be valid text.',

            'message.max' => 'Message may not be greater than 5000 characters.',

            /*
            |--------------------------------------------------------------------------
            | Image
            |--------------------------------------------------------------------------
            */

            'image.required_if' => 'An image is required for image messages.',

            'image.image' => 'The selected file must be an image.',

            'image.mimes' => 'The image must be a JPG, JPEG, PNG, or WEBP file.',

            'image.max' => 'The image may not be greater than 8 MB.',

            /*
            |--------------------------------------------------------------------------
            | Feed
            |--------------------------------------------------------------------------
            */

            'feed_id.required_if' => 'A feed post is required for feed messages.',

            'feed_id.exists' => 'The selected feed post does not exist.',

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            'product_id.required_if' => 'A product is required for product messages.',

            'product_id.exists' => 'The selected product does not exist.',
        ];
    }
}
