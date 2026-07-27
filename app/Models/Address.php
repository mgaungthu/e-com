<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'type',
    'label',
    'recipient_name',
    'phone',
    'alternate_phone',
    'address_line_one',
    'address_line_two',
    'building',
    'floor',
    'unit',
    'landmark',
    'township',
    'city',
    'state',
    'postal_code',
    'country_code',
    'latitude',
    'longitude',
    'delivery_instruction',
    'is_default_shipping',
    'is_default_billing',
])]
class Address extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_default_shipping' => 'boolean',
            'is_default_billing' => 'boolean',
        ];
    }
}
