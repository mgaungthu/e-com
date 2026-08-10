<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\StoreCartItemRequest;
use App\Http\Requests\Api\V1\Cart\UpdateCartItemRequest;
use App\Http\Resources\Api\V1\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getUserCart($request);

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => new CartResource($cart),
            ],
        ]);
    }

    public function store(StoreCartItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = Product::query()
            ->with('category')
            ->find($validated['product_id']);

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => [
                    'The selected product does not exist.',
                ],
            ]);
        }

        $this->ensureProductCanBePurchased($product);

        $cart = Cart::query()->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $cartItem = DB::transaction(function () use ($cart, $product, $validated) {
            $existingItem = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $newQuantity = $existingItem
                ? $existingItem->quantity + $validated['quantity']
                : $validated['quantity'];

            $this->ensureStockIsAvailable($product, $newQuantity);

            $unitPrice = $this->resolveProductPrice($product);

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $unitPrice,
                ]);

                return $existingItem;
            }

            return CartItem::query()->create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $unitPrice,
            ]);
        });

        $cart = $this->loadCart($cart);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully.',
            'data' => [
                'cart_item_id' => $cartItem->id,
                'cart' => new CartResource($cart),
            ],
        ], 201);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $this->ensureCartItemBelongsToUser($request, $cartItem);

        $cartItem->load('product.category');

        $this->ensureProductCanBePurchased($cartItem->product);

        $quantity = $request->integer('quantity');

        $this->ensureStockIsAvailable($cartItem->product, $quantity);

        $cartItem->update([
            'quantity' => $quantity,
            'unit_price' => $this->resolveProductPrice($cartItem->product),
        ]);

        $cart = $this->loadCart($cartItem->cart);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully.',
            'data' => [
                'cart' => new CartResource($cart),
            ],
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->ensureCartItemBelongsToUser($request, $cartItem);

        $cart = $cartItem->cart;

        $cartItem->delete();

        $cart = $this->loadCart($cart);

        return response()->json([
            'success' => true,
            'message' => 'Product removed from cart successfully.',
            'data' => [
                'cart' => new CartResource($cart),
            ],
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = Cart::query()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($cart) {
            $cart->items()->delete();
        }

        $cart = $this->getUserCart($request);

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully.',
            'data' => [
                'cart' => new CartResource($cart),
            ],
        ]);
    }

    private function getUserCart(Request $request): Cart
    {
        $cart = Cart::query()->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        return $this->loadCart($cart);
    }

    private function loadCart(Cart $cart): Cart
    {
        return $cart->load([
            'items' => fn ($query) => $query
                ->latest('id'),
            'items.product',
        ]);
    }

    private function ensureCartItemBelongsToUser(Request $request, CartItem $cartItem): void
    {
        $belongsToUser = $cartItem->cart()
            ->where('user_id', $request->user()->id)
            ->exists();

        abort_unless($belongsToUser, 404);
    }

    private function ensureProductCanBePurchased(Product $product): void
    {
        if (! $product->is_active) {
            throw ValidationException::withMessages([
                'product_id' => [
                    'This product is currently unavailable.',
                ],
            ]);
        }

        if (
            $product->category
            && ! $product->category->is_active
        ) {
            throw ValidationException::withMessages([
                'product_id' => [
                    'This product category is currently unavailable.',
                ],
            ]);
        }

        if ($product->stock_quantity < 1) {
            throw ValidationException::withMessages([
                'product_id' => [
                    'This product is currently out of stock.',
                ],
            ]);
        }
    }

    private function ensureStockIsAvailable(Product $product, int $quantity): void
    {
        if ($quantity > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => [
                    "Only {$product->stock_quantity} item(s) are available.",
                ],
            ]);
        }
    }

    private function resolveProductPrice(Product $product): float
    {
        $price = (float) $product->price;
        $salePrice = $product->sale_price !== null
            ? (float) $product->sale_price
            : null;

        if (
            $salePrice !== null
            && $salePrice >= 0
            && $salePrice < $price
        ) {
            return $salePrice;
        }

        return $price;
    }
}
