<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'type' => $this->type,

            'message' => $this->message,

            'sender' => [
                'type' => $this->sender_type,
                'id' => $this->sender?->id,
                'name' => $this->sender?->display_name
                    ?: $this->sender?->name,
                'avatar_url' => $this->sender?->avatar_url,
            ],

            'feed' => $this->when(
                $this->feed !== null,
                fn () => [
                    'id' => $this->feed->id,
                    'caption' => $this->feed->caption,
                ],
            ),

            'product' => $this->when(
                $this->product !== null,
                fn () => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                    'price' => $this->product->price,
                    'sale_price' => $this->product->sale_price,
                ],
            ),

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}