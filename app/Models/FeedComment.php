<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedComment extends Model
{
    protected $fillable = ['feed_id', 'user_id', 'comment', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }
}
