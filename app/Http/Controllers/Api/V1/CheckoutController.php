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
    public function __construct(private readonly CheckoutCalculator $checkoutCalculator) {}

    public function preview(CheckoutPreviewRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = $request->user()->id;

        $cart = Cart::query()
            ->where('user_id', $userId)
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
            ->where('user_id', $userId)
            ->find($validated['shipping_address_id']);

        if (! $shippingAddress) {
            throw ValidationException::withMessages([
                'shipping_address_id' => [
                    'The selected shipping address is invalid.',
                ],
            ]);
        }

        $billingAddress = Address::query()
            ->where('user_id', $userId)
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

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (! $product || ! $product->is_active) {
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

            if ($item->quantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'cart' => [
                        "Only {$product->stock_quantity} item(s) are available for {$product->name}.",
                    ],
                ]);
            }

        }
        $calculation = $this->checkoutCalculator->calculate($cart->items);

        $preview = [
            'cart' => new CartResource($cart),

            'shipping_address' => new AddressResource($shippingAddress),

            'billing_address' => new AddressResource($billingAddress),

            'payment_method' => new PaymentMethodResource($paymentMethod),

            ...$calculation,
        ];

        return response()->json([
            'success' => true,
            'data' => new CheckoutPreviewResource($preview),
        ]);
    }
}
