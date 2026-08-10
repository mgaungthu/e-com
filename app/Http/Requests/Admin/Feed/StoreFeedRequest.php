<?php

namespace App\Http\Requests\Admin\Feed;

use App\Enums\FeedStatus;
use Illuminate\Validation\Validator;

class StoreFeedRequest extends FeedRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('feeds.create') ?? false;
    }

    public function rules(): array
    {
        return $this->commonRules();
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('status') === FeedStatus::Published->value && ! $this->input('published_at')) {
                $validator->errors()->add('published_at', 'A published feed post requires a published time.');
            }

            if ($this->input('status') === FeedStatus::Published->value && count($this->file('images', [])) === 0) {
                $validator->errors()->add('images', 'A published feed post requires at least one image.');
            }
        }];
    }
}
