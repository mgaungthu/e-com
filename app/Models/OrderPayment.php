<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OrderPayment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method_id',
        'method_code',
        'method_name',
        'amount',
        'reference_number',
        'proof_image_path',
        'status',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $appends = [
        'proof_image_url',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getProofImageUrlAttribute(): ?string
    {
        if (! $this->proof_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->proof_image_path);
    }
}
