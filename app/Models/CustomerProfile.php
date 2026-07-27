<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'date_of_birth',
    'gender',
    'nationality',
    'preferred_language',
    'occupation',
    'company_name',
    'tax_id',
    'loyalty_points',
    'total_orders',
    'total_spent',
    'first_order_at',
    'last_order_at',
    'marketing_email_enabled',
    'marketing_sms_enabled',
    'marketing_push_enabled',
    'admin_note',
])]
#[Hidden([
    'tax_id',
    'admin_note',
])]
class CustomerProfile extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'loyalty_points' => 'integer',
            'total_orders' => 'integer',
            'total_spent' => 'decimal:2',
            'first_order_at' => 'datetime',
            'last_order_at' => 'datetime',
            'marketing_email_enabled' => 'boolean',
            'marketing_sms_enabled' => 'boolean',
            'marketing_push_enabled' => 'boolean',
        ];
    }
}
