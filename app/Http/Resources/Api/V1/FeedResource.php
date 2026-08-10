<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userId = $request->user()?->id;

        return ['id' => $this->id, 'caption' => $this->caption, 'published_at' => $this->published_at?->toISOString(), 'author' => ['id' => $this->author?->id, 'name' => $this->author?->display_name ?: $this->author?->name, 'avatar_url' => $this->author?->avatar_url], 'media' => $this->media->map(fn ($media) => ['id' => $media->id, 'type' => $media->type, 'url' => $media->url, 'width' => $media->width, 'height' => $media->height]), 'products' => $this->products->map(fn ($product) => ['id' => $product->id, 'name' => $product->name, 'slug' => $product->slug, 'price' => (float) $product->price, 'sale_price' => $product->sale_price !== null ? (float) $product->sale_price : null, 'image_url' => $product->image_url]), 'stats' => ['likes' => (int) ($this->likes_count ?? 0), 'comments' => (int) ($this->comments_count ?? 0)], 'viewer' => ['is_liked' => $userId ? $this->likes->contains('user_id', $userId) : false, 'is_bookmarked' => $userId ? $this->bookmarks->contains('user_id', $userId) : false]];
    }
}
