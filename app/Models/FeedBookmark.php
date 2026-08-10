<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedBookmark extends Model
{
    protected $fillable = ['feed_id', 'user_id'];
}
