<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\CustomerProfile;
use App\Models\Order;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private readonly OrderInventoryService $orderInventoryService) {}

    public function transitionStatus(Order $order, OrderStatus $targetStatus, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $targetStatus, $actor, $note): Order {
            $lockedOrder = Order::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($order->id);
            $currentStatus = $lockedOrder->status;

            if (! $currentStatus->canTransitionTo($targetStatus)) {
                throw new DomainException("Order cannot move from {$currentStatus->value} to {$targetStatus->value}.");
            }

            if (
                $targetStatus === OrderStatus::Confirmed &&
                $lockedOrder->items->isEmpty()
            ) {
                throw new DomainException('An order must contain at least one item before confirmation.');
            }

            if ($targetStatus === OrderStatus::Cancelled) {
                $this->orderInventoryService->restoreForCancelledOrder($lockedOrder, $actor);
            }

            $lockedOrder->update([
                'status' => $targetStatus,
                $this->timestampColumn($targetStatus) => now(),
            ]);

            $lockedOrder->statusHistories()->create([
                'changed_by' => $actor->id,
                'from_status' => $currentStatus,
                'to_status' => $targetStatus,
                'note' => $note,
            ]);

            if ($targetStatus === OrderStatus::Delivered && $lockedOrder->user_id) {
                $this->syncCustomerProfile($lockedOrder->user_id);
            }

            return $lockedOrder->refresh();
        });
    }

    public function transitionPayment(Order $order, PaymentStatus $targetStatus): Order
    {
        return DB::transaction(function () use ($order, $targetStatus): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $currentStatus = $lockedOrder->payment_status;

            if (! $currentStatus->canTransitionTo($targetStatus)) {
                throw new DomainException("Payment cannot move from {$currentStatus->value} to {$targetStatus->value}.");
            }

            if (
                $lockedOrder->status === OrderStatus::Cancelled &&
                $targetStatus === PaymentStatus::Paid
            ) {
                throw new DomainException('A cancelled order cannot be marked as paid.');
            }

            $lockedOrder->update([
                'payment_status' => $targetStatus,
                'paid_at' => $targetStatus === PaymentStatus::Paid
                    ? now()
                    : $lockedOrder->paid_at,
            ]);

            if (
                $lockedOrder->status === OrderStatus::Delivered &&
                $lockedOrder->user_id
            ) {
                $this->syncCustomerProfile($lockedOrder->user_id);
            }

            return $lockedOrder->refresh();
        });
    }

    private function timestampColumn(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Confirmed => 'confirmed_at',
            OrderStatus::Processing => 'processing_at',
            OrderStatus::Shipped => 'shipped_at',
            OrderStatus::Delivered => 'delivered_at',
            OrderStatus::Cancelled => 'cancelled_at',
            OrderStatus::Pending => throw new DomainException('Pending is not a transition target.'),
        };
    }

    private function syncCustomerProfile(int $customerId): void
    {
        $deliveredOrders = Order::query()
            ->where('user_id', $customerId)
            ->where('status', OrderStatus::Delivered);

        $summary = (clone $deliveredOrders)->selectRaw('COUNT(*) as order_count, MIN(delivered_at) as first_order_at, MAX(delivered_at) as last_order_at')->first();

        $totalSpent = (clone $deliveredOrders)
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('grand_total');

        CustomerProfile::query()->updateOrCreate(['user_id' => $customerId], [
            'total_orders' => (int) ($summary?->order_count ?? 0),
            'total_spent' => $totalSpent,
            'first_order_at' => $summary?->first_order_at,
            'last_order_at' => $summary?->last_order_at,
        ], );
    }
}
