<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Delivering = 'delivering';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [
                self::Confirmed,
                self::Cancelled,
            ],

            self::Confirmed => [
                self::Delivering,
                self::Cancelled,
            ],

            self::Delivering => [
                self::Delivered,
                self::Cancelled,
            ],

            self::Delivered,
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array(
            $status,
            $this->allowedTransitions(),
            true,
        );
    }
}