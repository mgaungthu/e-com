<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'logo_path',
        'account_name',
        'account_number',
        'qr_image_path',
        'instructions',
        'requires_proof',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requires_proof' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }
}