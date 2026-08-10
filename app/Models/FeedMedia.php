<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedMedia extends Model
{
    protected $fillable = ['feed_id', 'type', 'file_path', 'sort_order', 'width', 'height'];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'width' => 'integer', 'height' => 'integer'];
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => asset('storage/'.$this->file_path));
    }
}
