<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_id',
        'type',
        'message',
        'image_path',
        'image_width',
        'image_height',
        'image_size',
        'image_mime_type',
        'feed_id',
        'product_id',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'sender_id',
        );
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}