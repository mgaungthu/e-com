<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'currency',
    'language',
    'timezone',
    'preferred_payment_method',
    'preferred_delivery_method',
    'notification_email',
    'notification_sms',
    'notification_push',
    'dark_mode_enabled',
])]
class UserPreference extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'notification_email' => 'boolean',
            'notification_sms' => 'boolean',
            'notification_push' => 'boolean',
            'dark_mode_enabled' => 'boolean',
        ];
    }
}
