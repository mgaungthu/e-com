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

class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_support_can_update_customers_and_manage_notes(): void
    {
        $support = $this->userWithRole('customer_support');
        $customer = $this->userWithRole('customer');

        $this->actingAs($support)->patchJson("/admin/customers/{$customer->id}", [
            'first_name' => 'Aung', 'last_name' => 'Thu', 'display_name' => 'Aung Thu',
            'phone' => '09123456789', 'admin_note' => 'VIP customer',
        ])->assertOk();
        $this->assertDatabaseHas('customer_profiles', ['user_id' => $customer->id, 'admin_note' => 'VIP customer']);

        $this->actingAs($support)->patchJson("/admin/customers/{$customer->id}/status", ['status' => 'blocked'])->assertOk();
        $this->assertSame('blocked', $customer->fresh()->status);

        $noteId = $this->actingAs($support)->postJson("/admin/customers/{$customer->id}/notes", ['note' => 'Follow up', 'is_pinned' => true])
            ->assertCreated()->json('data.note.id');
        $this->actingAs($support)->patchJson("/admin/customers/{$customer->id}/notes/{$noteId}", ['note' => 'Updated note', 'is_pinned' => false])->assertOk();
        $this->actingAs($support)->deleteJson("/admin/customers/{$customer->id}/notes/{$noteId}")->assertOk();
        $this->assertDatabaseMissing('customer_notes', ['id' => $noteId]);
    }

    public function test_dashboard_returns_sales_and_inventory_analytics(): void
    {
        $admin = $this->userWithRole('admin');
        $customer = $this->userWithRole('customer');
        Product::query()->create(['name' => 'Low stock', 'slug' => 'low-stock', 'sku' => 'LOW-1', 'price' => 1000, 'stock_quantity' => 1, 'low_stock_threshold' => 2, 'is_active' => true, 'is_featured' => false]);
        Order::query()->create(['user_id' => $customer->id, 'status' => OrderStatus::Delivered, 'payment_status' => PaymentStatus::Paid, 'subtotal' => 5000, 'grand_total' => 5000, 'delivered_at' => now(), 'paid_at' => now()]);

        $this->actingAs($admin)->getJson('/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.summary.total_orders', 1)
            ->assertJsonPath('data.summary.low_stock_products', 1)
            ->assertJsonCount(7, 'data.sales_trend');
    }

    public function test_admin_can_create_staff_and_manage_custom_roles(): void
    {
        $admin = $this->userWithRole('admin');
        $roleId = $this->actingAs($admin)->postJson('/admin/roles', [
            'name' => 'warehouse_manager',
            'permissions' => ['inventory.view', 'inventory.adjust'],
        ])->assertCreated()->json('data.role.id');

        $this->actingAs($admin)->postJson('/admin/staff', [
            'name' => 'Warehouse User', 'email' => 'warehouse@example.com',
            'password' => 'password123', 'status' => 'active', 'role' => 'warehouse_manager',
        ])->assertCreated();
        $this->assertTrue(User::query()->where('email', 'warehouse@example.com')->firstOrFail()->hasRole('warehouse_manager'));

        $this->actingAs($admin)->patchJson("/admin/roles/{$roleId}", [
            'name' => 'warehouse_manager', 'permissions' => ['inventory.view'],
        ])->assertOk();
    }

    public function test_reports_can_be_viewed_and_exported_with_permissions(): void
    {
        $manager = $this->userWithRole('order_manager');
        $this->actingAs($manager)->getJson('/admin/reports')->assertOk()->assertJsonStructure(['data' => ['sales', 'inventory', 'customers']]);
        $this->actingAs($manager)->get('/admin/reports/export?type=sales')->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $support = $this->userWithRole('customer_support');
        $this->actingAs($support)->getJson('/admin/reports')->assertForbidden();
    }

    public function test_settings_are_persistent_and_currency_is_limited_to_mmk(): void
    {
        $admin = $this->userWithRole('admin');
        $this->actingAs($admin)->getJson('/admin/settings')->assertOk()->assertJsonPath('data.currency', 'MMK');

        $settings = [
            'store_name' => 'Myanmar Store', 'support_email' => 'help@example.com',
            'support_phone' => '091111111', 'currency' => 'MMK', 'timezone' => 'Asia/Yangon',
            'order_prefix' => 'SHOP', 'default_low_stock_threshold' => 10,
            'tax_rate' => 5, 'shipping_fee' => 2500,
        ];
        $this->actingAs($admin)->putJson('/admin/settings', $settings)->assertOk();
        $this->assertDatabaseHas('settings', ['key' => 'store_name', 'value' => 'Myanmar Store']);

        $settings['currency'] = 'USD';
        $this->actingAs($admin)->putJson('/admin/settings', $settings)
            ->assertUnprocessable()
            ->assertJsonPath('errors.currency.0', 'The selected currency is invalid.');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole($role);

        return $user;
    }
}
