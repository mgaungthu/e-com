<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_order_manager_can_list_filter_and_view_orders(): void
    {
        $manager = $this->userWithRole('order_manager');
        $customer = $this->userWithRole('customer');
        $order = $this->order($customer, ['status' => OrderStatus::Confirmed]);
        $this->addItem($order, $this->product());

        $this
            ->actingAs($manager)
            ->getJson('/admin/orders?status=confirmed&search='.$order->order_number)
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.order_number', $order->order_number);

        $this
            ->actingAs($manager)
            ->getJson("/admin/orders/{$order->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.order.items');
    }

    public function test_confirming_does_not_double_reserve_stock_and_cancelling_restores_once(): void
    {
        $manager = $this->userWithRole('order_manager');
        $customer = $this->userWithRole('customer');
        $product = $this->product(['stock_quantity' => 10]);
        $order = $this->order($customer);
        $this->addItem($order, $product, 3);
        $product->update(['stock_quantity' => 7]);
        $order->update(['inventory_reserved_at' => now()]);

        $this
            ->actingAs($manager)
            ->patchJson("/admin/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertOk()
            ->assertJsonPath('data.order.status', 'confirmed');

        $this->assertSame(7, $product->fresh()->stock_quantity);

        $this
            ->actingAs($manager)
            ->patchJson("/admin/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertOk()
            ->assertJsonPath('data.order.status', 'cancelled');

        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('inventory_transactions', 1);
        $this->assertDatabaseCount('order_status_histories', 2);

        $this
            ->actingAs($manager)
            ->patchJson("/admin/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertUnprocessable();

        $this->assertSame(10, $product->fresh()->stock_quantity);
    }

    public function test_confirming_legacy_order_does_not_change_stock(): void
    {
        $manager = $this->userWithRole('order_manager');
        $customer = $this->userWithRole('customer');
        $available = $this->product(['stock_quantity' => 10]);
        $insufficient = $this->product([
            'name' => 'Limited product',
            'slug' => 'limited-product',
            'sku' => 'LIMITED-001',
            'stock_quantity' => 1,
        ]);
        $order = $this->order($customer);
        $this->addItem($order, $available, 2);
        $this->addItem($order, $insufficient, 2);

        $this->actingAs($manager)
            ->patchJson("/admin/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertOk();

        $this->assertSame(10, $available->fresh()->stock_quantity);
        $this->assertSame(1, $insufficient->fresh()->stock_quantity);
        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
        $this->assertDatabaseCount('inventory_transactions', 0);
    }

    public function test_delivery_and_refund_keep_customer_totals_in_sync(): void
    {
        $manager = $this->userWithRole('order_manager');
        $customer = $this->userWithRole('customer');
        $product = $this->product(['stock_quantity' => 20]);
        $order = $this->order($customer, ['grand_total' => 30000]);
        $this->addItem($order, $product, 2, 15000);

        foreach (['confirmed', 'processing', 'shipped', 'delivered'] as $status) {
            $this->actingAs($manager)->patchJson("/admin/orders/{$order->id}/status", ['status' => $status])->assertOk();
        }

        $profile = $customer->customerProfile()->firstOrFail();
        $this->assertSame(1, $profile->total_orders);
        $this->assertSame('0.00', $profile->total_spent);

        $this->actingAs($manager)->patchJson("/admin/orders/{$order->id}/payment", ['payment_status' => 'paid'])->assertOk();

        $profile->refresh();
        $this->assertSame('30000.00', $profile->total_spent);

        $this->actingAs($manager)->patchJson("/admin/orders/{$order->id}/payment", ['payment_status' => 'refunded'])->assertOk();

        $profile->refresh();
        $this->assertSame(1, $profile->total_orders);
        $this->assertSame('0.00', $profile->total_spent);
    }

    public function test_invalid_transitions_and_missing_permissions_are_rejected(): void
    {
        $manager = $this->userWithRole('order_manager');
        $support = $this->userWithRole('customer_support');
        $productManager = $this->userWithRole('product_manager');
        $customer = $this->userWithRole('customer');
        $order = $this->order($customer);

        $this
            ->actingAs($manager)
            ->patchJson("/admin/orders/{$order->id}/status", ['status' => 'shipped'])
            ->assertUnprocessable();

        $this->actingAs($support)->getJson('/admin/orders')->assertOk();
        $this
            ->actingAs($support)
            ->patchJson("/admin/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertForbidden();
        $this->actingAs($productManager)->getJson('/admin/orders')->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole($role);

        return $user;
    }

    private function product(array $overrides = []): Product
    {
        return Product::query()->create([
            'name' => 'Order product', 'slug' => 'order-product', 'sku' => 'ORDER-001',
            'price' => 15000, 'stock_quantity' => 10, 'low_stock_threshold' => 2,
            'is_active' => true, 'is_featured' => false,
            ...$overrides,
        ]);
    }

    private function order(User $customer, array $overrides = []): Order
    {
        return Order::query()->create([
            'user_id' => $customer->id,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'subtotal' => 15000,
            'grand_total' => 15000,
            ...$overrides,
        ]);
    }

    private function addItem(Order $order, Product $product, int $quantity = 1, int $unitPrice = 15000): void
    {
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => $unitPrice * $quantity,
        ]);
    }
}
