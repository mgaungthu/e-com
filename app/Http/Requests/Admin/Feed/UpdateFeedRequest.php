<?php

namespace App\Http\Requests\Admin\Feed;

use App\Enums\FeedStatus;
use Illuminate\Validation\Validator;

class UpdateFeedRequest extends FeedRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('feeds.update') ?? false;
    }

    public function rules(): array
    {
        return $this->commonRules();
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $feed = $this->route('feed');
            $owned = $feed->media()->pluck('id');
            $removed = collect($this->input('remove_media_ids', []))->map(fn ($id) => (int) $id);
            $order = collect($this->input('media_order', []))->map(fn ($id) => (int) $id);
            if ($removed->diff($owned)->isNotEmpty() || $order->diff($owned)->isNotEmpty()) {
                $validator->errors()->add('media_order', 'Media must belong to this feed.');
            }

            if ($this->input('status') === FeedStatus::Published->value && ! $this->input('published_at')) {
                $validator->errors()->add('published_at', 'A published feed post requires a published time.');
            }

            if ($this->input('status') === FeedStatus::Published->value && $owned->diff($removed)->count() + count($this->file('images', [])) === 0) {
                $validator->errors()->add('images', 'A published feed post requires at least one image.');
            }
        }];
    }
}
