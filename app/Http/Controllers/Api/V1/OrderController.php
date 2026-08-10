<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\StoreOrderRequest;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\PaymentMethod;
use App\Services\CheckoutCalculator;
use App\Services\OrderInventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(private readonly CheckoutCalculator $checkoutCalculator, private readonly OrderInventoryService $orderInventoryService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with([
                'items',
                'latestPayment.paymentMethod',
            ])
            ->latest('id')
            ->paginate(perPage: (int) $request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders,
            ],
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            abort(404);
        }

        $order->load([
            'items',
            'latestPayment.paymentMethod',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
            ],
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $cart = Cart::query()
            ->where('user_id', $user->id)
            ->with([
                'items.product.category',
            ])
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => [
                    'Your cart is empty.',
                ],
            ]);
        }

        $shippingAddress = Address::query()
            ->where('user_id', $user->id)
            ->find($validated['shipping_address_id']);

        if (! $shippingAddress) {
            throw ValidationException::withMessages([
                'shipping_address_id' => [
                    'The selected shipping address is invalid.',
                ],
            ]);
        }

        $billingAddress = Address::query()
            ->where('user_id', $user->id)
            ->find($validated['billing_address_id']);

        if (! $billingAddress) {
            throw ValidationException::withMessages([
                'billing_address_id' => [
                    'The selected billing address is invalid.',
                ],
            ]);
        }

        $paymentMethod = PaymentMethod::query()
            ->where('is_active', true)
            ->find($validated['payment_method_id']);

        if (! $paymentMethod) {
            throw ValidationException::withMessages([
                'payment_method_id' => [
                    'The selected payment method is unavailable.',
                ],
            ]);
        }

        $proofPath = $request
            ->file('payment_proof')
            ->store('payment-proofs', 'public');

        try {
            $order = DB::transaction(function () use ($user, $cart, $shippingAddress, $billingAddress, $paymentMethod, $validated, $proofPath) {
                $lockedCart = Cart::query()
                    ->with('items')
                    ->lockForUpdate()
                    ->findOrFail($cart->id);

                if ($lockedCart->items->isEmpty()) {
                    throw ValidationException::withMessages([
                        'cart' => ['Your cart is empty.'],
                    ]);
                }

                $this->orderInventoryService->lockAndValidateCartItems($lockedCart->items);
                $calculation = $this->checkoutCalculator->calculate($lockedCart->items);

                $order = Order::query()->create([
                    'user_id' => $user->id,

                    'shipping_address' => $this->addressSnapshot($shippingAddress),

                    'billing_address' => $this->addressSnapshot($billingAddress),

                    'status' => OrderStatus::Pending,
                    'payment_status' => PaymentStatus::Pending,

                    'payment_method' => $paymentMethod->code,

                    'subtotal' => $calculation['subtotal'],
                    'discount_total' => $calculation['discount_total'],
                    'shipping_total' => $calculation['shipping_total'],
                    'tax_total' => $calculation['tax_total'],
                    'grand_total' => $calculation['grand_total'],

                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($calculation['lines'] as $line) {
                    $product = $line['cart_item']->product;
                    $cartItem = $line['cart_item'];

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'unit_price' => $line['unit_price'],
                        'quantity' => $cartItem->quantity,
                        'line_total' => $line['line_total'],
                    ]);
                }

                $this->orderInventoryService->reserveForNewOrder($order, $lockedCart->items, $user);

                OrderPayment::query()->create([
                    'order_id' => $order->id,
                    'payment_method_id' => $paymentMethod->id,

                    'method_code' => $paymentMethod->code,
                    'method_name' => $paymentMethod->name,

                    'amount' => $calculation['grand_total'],

                    'reference_number' => $validated['payment_reference']
                        ?? null,

                    'proof_image_path' => $proofPath,

                    'status' => 'submitted',

                    'submitted_at' => now(),
                ]);

                $lockedCart->items()->delete();

                return $order;
            });

            $order->load([
                'items',
                'latestPayment.paymentMethod',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order submitted successfully and is awaiting payment verification.',
                'data' => [
                    'order' => $order,
                ],
            ], 201);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($proofPath);

            throw $exception;
        }
    }

    private function addressSnapshot(Address $address): array
    {
        return [
            'id' => $address->id,
            'type' => $address->type,
            'label' => $address->label,
            'recipient_name' => $address->recipient_name,
            'phone' => $address->phone,
            'alternate_phone' => $address->alternate_phone,
            'address_line_one' => $address->address_line_one,
            'address_line_two' => $address->address_line_two,
            'building' => $address->building,
            'floor' => $address->floor,
            'unit' => $address->unit,
            'landmark' => $address->landmark,
            'township' => $address->township,
            'city' => $address->city,
            'state' => $address->state,
            'postal_code' => $address->postal_code,
            'country_code' => $address->country_code,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
            'delivery_instruction' => $address->delivery_instruction,
        ];
    }
}
