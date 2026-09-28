<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Checkout\CheckoutPreviewRequest;
use App\Http\Resources\Api\V1\AddressResource;
use App\Http\Resources\Api\V1\CartResource;
use App\Http\Resources\Api\V1\CheckoutPreviewResource;
use App\Http\Resources\Api\V1\PaymentMethodResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\PaymentMethod;
use App\Services\CheckoutCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutCalculator $checkoutCalculator,
    ) {}

    public function preview(
        CheckoutPreviewRequest $request,
    ): JsonResponse {
        $validated = $request->validated();

        $userId = $request->user()->id;

        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        */

        $cart = Cart::query()
            ->where('user_id', $userId)
            ->with([
                'items.product.category',
            ])
            ->first();

        if (
            ! $cart
            || $cart->items->isEmpty()
        ) {
            throw ValidationException::withMessages([
                'cart' => [
                    'Your cart is empty.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Shipping Address
        |--------------------------------------------------------------------------
        */

        $shippingAddress = Address::query()
            ->where('user_id', $userId)
            ->find(
                $validated['shipping_address_id'],
            );

        if (! $shippingAddress) {
            throw ValidationException::withMessages([
                'shipping_address_id' => [
                    'The selected shipping address is invalid.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Billing Address
        |--------------------------------------------------------------------------
        */

        $billingAddress = Address::query()
            ->where('user_id', $userId)
            ->find(
                $validated['billing_address_id'],
            );

        if (! $billingAddress) {
            throw ValidationException::withMessages([
                'billing_address_id' => [
                    'The selected billing address is invalid.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Method
        |--------------------------------------------------------------------------
        |
        | The payment method is optional for checkout preview.
        |
        | Address / Review:
        |   The mobile app can calculate the complete order summary before the
        |   customer selects a payment method.
        |
        | Payment:
        |   Once a payment method is selected, the same preview endpoint is
        |   called again with payment_method_id.
        |
        | Actual order creation still requires payment_method_id through
        | StoreOrderRequest.
        |--------------------------------------------------------------------------
        */

        $paymentMethod = null;

        $paymentMethodId =
            $validated['payment_method_id']
            ?? null;

        if ($paymentMethodId !== null) {
            $paymentMethod = PaymentMethod::query()
                ->where('is_active', true)
                ->find($paymentMethodId);

            if (! $paymentMethod) {
                throw ValidationException::withMessages([
                    'payment_method_id' => [
                        'The selected payment method is unavailable.',
                    ],
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Cart Validation
        |--------------------------------------------------------------------------
        */

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (
                ! $product
                || ! $product->is_active
            ) {
                throw ValidationException::withMessages([
                    'cart' => [
                        'One or more products in your cart are unavailable.',
                    ],
                ]);
            }

            if (
                $product->category
                && ! $product->category->is_active
            ) {
                throw ValidationException::withMessages([
                    'cart' => [
                        'One or more product categories are unavailable.',
                    ],
                ]);
            }

            if (
                $item->quantity
                > $product->stock_quantity
            ) {
                throw ValidationException::withMessages([
                    'cart' => [
                        "Only {$product->stock_quantity} item(s) are available for {$product->name}.",
                    ],
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate Checkout
        |--------------------------------------------------------------------------
        */

        $calculation =
            $this->checkoutCalculator->calculate(
                $cart->items,
                $shippingAddress,
            );

        /*
        |--------------------------------------------------------------------------
        | Preview Response
        |--------------------------------------------------------------------------
        */

        $preview = [
            'cart' => new CartResource($cart),

            'shipping_address' =>
                new AddressResource(
                    $shippingAddress,
                ),

            'billing_address' =>
                new AddressResource(
                    $billingAddress,
                ),

            'payment_method' =>
                $paymentMethod
                    ? new PaymentMethodResource(
                        $paymentMethod,
                    )
                    : null,

            ...$calculation,
        ];

        return response()->json([
            'success' => true,

            'data' =>
                new CheckoutPreviewResource(
                    $preview,
                ),
        ]);
    }
}