<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedLike extends Model
{
    protected $fillable = ['feed_id', 'user_id'];
}
