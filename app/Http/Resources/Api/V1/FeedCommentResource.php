<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'comment' => $this->comment, 'created_at' => $this->created_at?->toISOString(), 'user' => ['id' => $this->user?->id, 'name' => $this->user?->display_name ?: $this->user?->name, 'avatar_url' => $this->user?->avatar_url]];
    }
}
