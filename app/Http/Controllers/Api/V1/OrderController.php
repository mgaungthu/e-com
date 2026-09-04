<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderPlaced;
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

        /*
        |--------------------------------------------------------------------------
        | Load Cart
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Validate Shipping Address
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Validate Billing Address
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Validate Payment Method
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Store Payment Proof
        |--------------------------------------------------------------------------
        */

        $proofPath = $request
            ->file('payment_proof')
            ->store('payment-proofs', 'public');

        /*
        |--------------------------------------------------------------------------
        | Create Order Transaction
        |--------------------------------------------------------------------------
        |
        | Important:
        | Only delete payment proof when the actual order transaction fails.
        |--------------------------------------------------------------------------
        */

        try {
            $order = DB::transaction(function () use ($user, $cart, $shippingAddress, $billingAddress, $paymentMethod, $validated, $proofPath, ) {
                /*
                |--------------------------------------------------------------------------
                | Lock Cart
                |--------------------------------------------------------------------------
                */

                $lockedCart = Cart::query()
                    ->with('items')
                    ->lockForUpdate()
                    ->findOrFail($cart->id);

                if ($lockedCart->items->isEmpty()) {
                    throw ValidationException::withMessages([
                        'cart' => [
                            'Your cart is empty.',
                        ],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Inventory
                |--------------------------------------------------------------------------
                */

                $this->orderInventoryService
                    ->lockAndValidateCartItems($lockedCart->items);

                /*
                |--------------------------------------------------------------------------
                | Calculate Checkout
                |--------------------------------------------------------------------------
                */

                $calculation = $this->checkoutCalculator
                    ->calculate($lockedCart->items);

                /*
                |--------------------------------------------------------------------------
                | Create Order
                |--------------------------------------------------------------------------
                */

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

                    'notes' => $validated['notes']
                        ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Create Order Items
                |--------------------------------------------------------------------------
                */

                foreach ($calculation['lines'] as $line) {
                    $product =
                        $line['cart_item']->product;

                    $cartItem =
                        $line['cart_item'];

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

                /*
                |--------------------------------------------------------------------------
                | Reserve Inventory
                |--------------------------------------------------------------------------
                */

                $this->orderInventoryService
                    ->reserveForNewOrder($order, $lockedCart->items, $user);

                /*
                |--------------------------------------------------------------------------
                | Create Payment Submission
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Clear Cart
                |--------------------------------------------------------------------------
                */

                $lockedCart
                    ->items()
                    ->delete();

                return $order;
            }, );
        } catch (\Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Transaction failed.
            |
            | The order does not exist, so remove the uploaded payment proof.
            |--------------------------------------------------------------------------
            */

            Storage::disk('public')
                ->delete($proofPath);

            throw $exception;
        }

        /*
        |--------------------------------------------------------------------------
        | Transaction is now committed successfully
        |--------------------------------------------------------------------------
        */

        $order->load([
            'customer',
            'items',
            'latestPayment.paymentMethod',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Dispatch Order Confirmation Event
        |--------------------------------------------------------------------------
        |
        | Email / queue failure should NOT make the customer think the order
        | itself failed, because the order is already committed in the database.
        |--------------------------------------------------------------------------
        */

        try {
            OrderPlaced::dispatch($order);
        } catch (\Throwable $exception) {
            report($exception);
        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' => 'Order submitted successfully and is awaiting payment verification.',

            'data' => [
                'order' => $order,
            ],
        ], 201);
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
