<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'device_id',
    'platform',
    'device_name',
    'device_model',
    'os_version',
    'app_version',
    'push_token',
    'last_active_at',
    'is_active',
])]
#[Hidden([
    'push_token',
])]
class UserDevice extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'last_active_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
