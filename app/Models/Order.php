<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $appends = [
        'available_statuses',
        'available_payment_statuses',
    ];

    protected $fillable = [
        'order_number',
        'user_id',

        'shipping_address',
        'billing_address',

        'status',
        'payment_status',
        'payment_method',

        'subtotal',
        'discount_total',
        'shipping_total',
        'tax_total',
        'grand_total',

        'notes',

        'confirmed_at',
        'processing_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'paid_at',
        'inventory_reserved_at',
        'inventory_restored_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if ($order->order_number) {
                return;
            }

            do {
                $number = 'ORD-'
                    .now()->format('Ymd')
                    .'-'
                    .Str::upper(Str::random(6));
            } while (
                self::query()
                    ->where('order_number', $number)
                    ->exists()
            );

            $order->order_number = $number;
        });
    }

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,

            'shipping_address' => 'array',
            'billing_address' => 'array',

            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'grand_total' => 'decimal:2',

            'confirmed_at' => 'datetime',
            'processing_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'paid_at' => 'datetime',
            'inventory_reserved_at' => 'datetime',
            'inventory_restored_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)
            ->latest();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(OrderPayment::class)
            ->latestOfMany();
    }

    public function getAvailableStatusesAttribute(): array
    {
        return array_map(fn (OrderStatus $status): string => $status->value, $this->status->allowedTransitions());
    }

    public function getAvailablePaymentStatusesAttribute(): array
    {
        return array_map(fn (PaymentStatus $status): string => $status->value, $this->payment_status->allowedTransitions());
    }
}
