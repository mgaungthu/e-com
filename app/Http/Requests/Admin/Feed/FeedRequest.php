<?php

namespace App\Http\Requests\Admin\Feed;

use App\Enums\FeedStatus;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class FeedRequest extends FormRequest
{
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (! empty($data['published_at'])) {
            $data['published_at'] = CarbonImmutable::parse($data['published_at'])
                ->utc()->format('Y-m-d\TH:i:s.u\Z');
        }

        return data_get($data, $key, $default);
    }

    protected function commonRules(): array
    {
        return [
            'caption' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(FeedStatus::class)],
            'is_active' => ['required', 'boolean'],
            'published_at' => [
                'bail',
                'nullable',
                'date',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,6})?(?:Z|[+-](?:[01]\d|2[0-3]):[0-5]\d)$/', $value)) {
                        $fail('The published at field must be an ISO 8601 timestamp with a timezone.');
                    }
                },
            ],
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
