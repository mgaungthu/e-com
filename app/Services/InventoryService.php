<?php

namespace App\Services;

use App\Enums\InventoryTransactionType;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    public function adjust(
        Product $product,
        InventoryTransactionType $type,
        int $quantity,
        ?User $user = null,
        ?string $reason = null,
        ?string $note = null,
    ): InventoryTransaction {
        return DB::transaction(function () use (
            $product,
            $type,
            $quantity,
            $user,
            $reason,
            $note,
        ) {
            $lockedProduct = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            $quantityBefore =
                $lockedProduct->stock_quantity;

            $quantityAfter = match ($type) {
                InventoryTransactionType::Addition =>
                    $quantityBefore + $quantity,

                InventoryTransactionType::Subtraction =>
                    $quantityBefore - $quantity,

                InventoryTransactionType::Set =>
                    $quantity,
            };

            if ($quantityAfter < 0) {
                throw new InvalidArgumentException(
                    'Stock quantity cannot be negative.',
                );
            }

            $transactionQuantity = match ($type) {
                InventoryTransactionType::Addition =>
                    $quantity,

                InventoryTransactionType::Subtraction =>
                    -$quantity,

                InventoryTransactionType::Set =>
                    $quantity,
            };

            $lockedProduct->update([
                'stock_quantity' => $quantityAfter,
            ]);

            return InventoryTransaction::query()->create([
                'product_id' => $lockedProduct->id,
                'user_id' => $user?->id,
                'type' => $type,
                'quantity' => $transactionQuantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reason' => $reason,
                'note' => $note,
            ]);
        });
    }
}