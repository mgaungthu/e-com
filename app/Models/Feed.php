<?php

namespace App\Models;

use App\Enums\FeedStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feed extends Model
{
    protected $fillable = ['user_id', 'caption', 'status', 'is_active', 'published_at'];

    protected function casts(): array
    {
        return ['status' => FeedStatus::class, 'is_active' => 'boolean', 'published_at' => 'datetime'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(FeedMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'feed_products')->withPivot('sort_order')->withTimestamps()->orderByPivot('sort_order')->orderBy('products.id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(FeedLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FeedComment::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(FeedBookmark::class);
    }

    public function scopeVisible(Builder $query): void
    {
        $query->where('status', FeedStatus::Published)->where('is_active', true)->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
