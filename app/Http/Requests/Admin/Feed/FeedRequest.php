<?php

namespace App\Http\Requests\Admin\Feed;

use App\Enums\FeedStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class FeedRequest extends FormRequest
{
    protected function commonRules(): array
    {
        return [
            'caption' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(FeedStatus::class)],
            'is_active' => ['required', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'products' => ['nullable', 'array', 'max:20'],
            'products.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'products.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'remove_media_ids' => ['nullable', 'array'],
            'remove_media_ids.*' => ['integer'],
            'media_order' => ['nullable', 'array'],
            'media_order.*' => ['integer'],
        ];
    }
}
