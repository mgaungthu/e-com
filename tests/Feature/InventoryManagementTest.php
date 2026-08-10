<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_product_manager_can_adjust_inventory_and_view_history(): void
    {
        $user = $this->userWithRole('product_manager');
        $product = $this->product(['stock_quantity' => 10]);

        $this
            ->actingAs($user)
            ->postJson("/admin/inventory/{$product->id}/adjust", [
                'type' => 'subtraction',
                'quantity' => 4,
                'reason' => 'Damaged items',
                'note' => 'Removed during stock count.',
            ])
            ->assertOk()
            ->assertJsonPath('data.product.stock_quantity', 6)
            ->assertJsonPath('data.transaction.quantity_before', 10)
            ->assertJsonPath('data.transaction.quantity_after', 6);

        $this->assertDatabaseHas('inventory_transactions', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => -4,
            'quantity_before' => 10,
            'quantity_after' => 6,
        ]);

        $this
            ->actingAs($user)
            ->getJson("/admin/inventory/{$product->id}/history")
            ->assertOk()
            ->assertJsonCount(1, 'data.data');
    }

    public function test_inventory_cannot_be_reduced_below_zero(): void
    {
        $user = $this->userWithRole('product_manager');
        $product = $this->product(['stock_quantity' => 2]);

        $this
            ->actingAs($user)
            ->postJson("/admin/inventory/{$product->id}/adjust", [
                'type' => 'subtraction',
                'quantity' => 3,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $this->assertSame(2, $product->fresh()->stock_quantity);
    }

    public function test_inventory_routes_enforce_view_and_adjust_permissions(): void
    {
        $user = $this->userWithRole('order_manager');
        $product = $this->product();

        $this->actingAs($user)->getJson('/admin/inventory')->assertForbidden();
        $this
            ->actingAs($user)
            ->getJson("/admin/inventory/{$product->id}/history")
            ->assertForbidden();
        $this
            ->actingAs($user)
            ->postJson("/admin/inventory/{$product->id}/adjust", [
                'type' => 'addition',
                'quantity' => 1,
            ])
            ->assertForbidden();
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
            'category_id' => null,
            'name' => 'Inventory product',
            'slug' => 'inventory-product',
            'sku' => 'INV-001',
            'barcode' => null,
            'short_description' => null,
            'description' => null,
            'price' => 15000,
            'sale_price' => null,
            'stock_quantity' => 5,
            'low_stock_threshold' => 2,
            'image_path' => null,
            'is_active' => true,
            'is_featured' => false,
            'seo_title' => null,
            'seo_description' => null,
            ...$overrides,
        ]);
    }
}
