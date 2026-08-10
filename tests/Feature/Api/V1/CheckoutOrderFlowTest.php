<?php

namespace Tests\Feature\Api\V1;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_and_order_store_the_same_totals_and_reserve_stock(): void
    {
        [$user, $address, $paymentMethod] = $this->checkoutContext();
        $product = $this->product(['price' => 10000, 'stock_quantity' => 10]);
        $this->cartWithItem($user, $product, 2);
        $this->setCommerceSettings(shippingFee: 1500, taxRate: 5);

        Sanctum::actingAs($user);

        $preview = $this->postJson('/api/v1/checkout/preview', $this->checkoutPayload($address, $paymentMethod))
            ->assertOk()
            ->assertJsonPath('data.summary.subtotal', 20000)
            ->assertJsonPath('data.summary.shipping_total', 1500)
            ->assertJsonPath('data.summary.tax_rate', 5)
            ->assertJsonPath('data.summary.tax_total', 1000)
            ->assertJsonPath('data.summary.grand_total', 22500);

        Storage::fake('public');
        $orderResponse = $this->post('/api/v1/orders', [
            ...$this->checkoutPayload($address, $paymentMethod),
            'payment_reference' => 'REF-123',
            'payment_proof' => UploadedFile::fake()->image('proof.png'),
        ])->assertCreated();

        $order = Order::query()->firstOrFail();
        $this->assertSame((float) $preview->json('data.summary.subtotal'), (float) $order->subtotal);
        $this->assertSame((float) $preview->json('data.summary.discount_total'), (float) $order->discount_total);
        $this->assertSame((float) $preview->json('data.summary.shipping_total'), (float) $order->shipping_total);
        $this->assertSame((float) $preview->json('data.summary.tax_total'), (float) $order->tax_total);
        $this->assertSame((float) $preview->json('data.summary.grand_total'), (float) $order->grand_total);
        $this->assertNotNull($order->inventory_reserved_at);
        $this->assertSame(8, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('order_payments', 1);
        $this->assertDatabaseCount('inventory_transactions', 1);
        $orderResponse->assertJsonPath('data.order.grand_total', '22500.00');
    }

    public function test_insufficient_stock_creates_no_order_payment_or_stock_change(): void
    {
        [$user, $address, $paymentMethod] = $this->checkoutContext();
        $product = $this->product(['stock_quantity' => 2]);
        $this->cartWithItem($user, $product, 3);
        Storage::fake('public');
        Sanctum::actingAs($user);

        $this->post('/api/v1/orders', [
            ...$this->checkoutPayload($address, $paymentMethod),
            'payment_proof' => UploadedFile::fake()->image('proof.png'),
        ])->assertUnprocessable()->assertJsonValidationErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_payments', 0);
        $this->assertDatabaseCount('inventory_transactions', 0);
        $this->assertSame(2, $product->fresh()->stock_quantity);
    }

    public function test_payment_failure_rolls_back_the_order_and_stock_reservation(): void
    {
        [$user, $address, $paymentMethod] = $this->checkoutContext();
        $product = $this->product(['stock_quantity' => 5]);
        $this->cartWithItem($user, $product, 3);
        Storage::fake('public');
        Sanctum::actingAs($user);

        OrderPayment::creating(static function (): never {
            throw new \RuntimeException('Simulated payment persistence failure.');
        });

        try {
            $this->withoutExceptionHandling()->post('/api/v1/orders', [
                ...$this->checkoutPayload($address, $paymentMethod),
                'payment_proof' => UploadedFile::fake()->image('proof.png'),
            ]);
            $this->fail('The payment persistence failure should be rethrown.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated payment persistence failure.', $exception->getMessage());
        } finally {
            OrderPayment::flushEventListeners();
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('order_payments', 0);
        $this->assertDatabaseCount('inventory_transactions', 0);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_customer_order_list_and_detail_are_scoped_to_the_owner(): void
    {
        [$user, $address, $paymentMethod] = $this->checkoutContext();
        $product = $this->product(['stock_quantity' => 4]);
        $this->cartWithItem($user, $product, 1);
        Storage::fake('public');
        Sanctum::actingAs($user);

        $orderId = $this->post('/api/v1/orders', [
            ...$this->checkoutPayload($address, $paymentMethod),
            'payment_proof' => UploadedFile::fake()->image('proof.png'),
        ])->assertCreated()->json('data.order.id');

        $this->getJson('/api/v1/orders')
            ->assertOk()
            ->assertJsonPath('data.orders.data.0.id', $orderId)
            ->assertJsonPath('data.orders.data.0.status', 'pending');
        $this->getJson("/api/v1/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('data.order.items.0.quantity', 1)
            ->assertJsonPath('data.order.latest_payment.status', 'submitted');

        Sanctum::actingAs(User::factory()->create());
        $this->getJson("/api/v1/orders/{$orderId}")->assertNotFound();
    }

    private function checkoutContext(): array
    {
        $user = User::factory()->create();
        $address = Address::query()->create([
            'user_id' => $user->id,
            'recipient_name' => 'Customer',
            'phone' => '0912345678',
            'address_line_one' => '1 Main Street',
            'city' => 'Yangon',
        ]);
        $paymentMethod = PaymentMethod::query()->create([
            'name' => 'KBZPay', 'code' => 'kbzpay', 'is_active' => true,
        ]);

        return [$user, $address, $paymentMethod];
    }

    private function product(array $overrides = []): Product
    {
        return Product::query()->create([
            'name' => 'Checkout product', 'slug' => 'checkout-product-'.uniqid(), 'sku' => 'CHK-'.uniqid(),
            'price' => 10000, 'stock_quantity' => 10, 'is_active' => true,
            ...$overrides,
        ]);
    }

    private function cartWithItem(User $user, Product $product, int $quantity): void
    {
        $cart = Cart::query()->create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => $product->price]);
    }

    private function checkoutPayload(Address $address, PaymentMethod $paymentMethod): array
    {
        return ['shipping_address_id' => $address->id, 'billing_address_id' => $address->id, 'payment_method_id' => $paymentMethod->id];
    }

    private function setCommerceSettings(float $shippingFee, float $taxRate): void
    {
        Setting::query()->where('key', 'shipping_fee')->update(['value' => (string) $shippingFee]);
        Setting::query()->where('key', 'tax_rate')->update(['value' => (string) $taxRate]);
    }
}
