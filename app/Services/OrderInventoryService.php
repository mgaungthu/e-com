<?php

namespace App\Services;

use App\Enums\InventoryTransactionType;
use App\Models\CartItem;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class OrderInventoryService
{
    /** @param Collection<int, CartItem> $cartItems */
    public function lockAndValidateCartItems(Collection $cartItems): void
    {
        $products = Product::query()
            ->with('category')
            ->whereIn('id', $cartItems->pluck('product_id')->unique()->sort()->values())
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($cartItems as $cartItem) {
            $product = $products->get($cartItem->product_id);

            if (! $product) {
                throw ValidationException::withMessages(['cart' => ['One or more products in your cart no longer exist.']]);
            }

            if (! $product->is_active || ($product->category && ! $product->category->is_active)) {
                throw ValidationException::withMessages(['cart' => ["{$product->name} is currently unavailable."]]);
            }

            if ($cartItem->quantity > $product->stock_quantity) {
                throw ValidationException::withMessages(['cart' => ["Only {$product->stock_quantity} item(s) are available for {$product->name}."]]);
            }

            $cartItem->setRelation('product', $product);
        }
    }

    /** @param Collection<int, CartItem> $cartItems */
    public function reserveForNewOrder(Order $order, Collection $cartItems, ?User $actor = null): void
    {
        foreach ($cartItems as $cartItem) {
            /** @var Product $product */
            $product = $cartItem->product;

            // Recheck immediately before the write, while the product row is locked.
            if ($cartItem->quantity > $product->stock_quantity) {
                throw ValidationException::withMessages(['cart' => ["Only {$product->stock_quantity} item(s) are available for {$product->name}."]]);
            }

            $this->changeStock($product, -$cartItem->quantity, $actor, "Order {$order->order_number}", 'Stock reserved at checkout.');
        }

        $order->update(['inventory_reserved_at' => now()]);
    }

    public function restoreForCancelledOrder(Order $order, ?User $actor = null): void
    {
        if (! $order->inventory_reserved_at || $order->inventory_restored_at) {
            return;
        }

        $items = $order->items;
        $products = Product::query()
            ->whereIn('id', $items->pluck('product_id')->filter()->unique()->sort()->values())
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item->product_id);

            if (! $product) {
                throw new InvalidArgumentException("Product {$item->product_name} is no longer available.");
            }

            $this->changeStock($product, $item->quantity, $actor, "Order {$order->order_number}", 'Stock restored when order was cancelled.');
        }

        $order->update(['inventory_restored_at' => now()]);
    }

    private function changeStock(Product $product, int $change, ?User $actor, string $reason, string $note): void
    {
        $before = $product->stock_quantity;
        $after = $before + $change;

        if ($after < 0) {
            throw new InvalidArgumentException('Stock quantity cannot be negative.');
        }

        $product->update(['stock_quantity' => $after]);

        InventoryTransaction::query()->create([
            'product_id' => $product->id,
            'user_id' => $actor?->id,
            'type' => $change < 0 ? InventoryTransactionType::Subtraction : InventoryTransactionType::Addition,
            'quantity' => $change,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reason' => $reason,
            'note' => $note,
        ]);
    }
}
