<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\FeedController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:5,1');

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Public catalog
    |--------------------------------------------------------------------------
    */

    Route::get('/categories', [CategoryController::class, 'index']);

    Route::get('/categories/{slug}', [CategoryController::class, 'show']);

    Route::get('/products', [ProductController::class, 'index']);

    /*
     * This route must be declared before /products/{slug}.
     */
    Route::get('/products/featured', [ProductController::class, 'featured']);

    Route::get('/products/{slug}', [ProductController::class, 'show']);

    Route::get('/feeds', [FeedController::class, 'index']);
    Route::get('/feeds/{feed}', [FeedController::class, 'show']);
    Route::get('/feeds/{feed}/comments', [FeedController::class, 'comments']);

    /*
    |--------------------------------------------------------------------------
    | Public payment methods
    |--------------------------------------------------------------------------
    */

    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Authenticated customer routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        */

        Route::get('/cart', [CartController::class, 'index']);

        Route::post('/cart/items', [CartController::class, 'store']);

        Route::patch('/cart/items/{cartItem}', [CartController::class, 'update']);

        Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy']);

        Route::delete('/cart', [CartController::class, 'clear']);

        /*
        |--------------------------------------------------------------------------
        | Addresses
        |--------------------------------------------------------------------------
        */

        Route::get('/addresses', [AddressController::class, 'index']);

        Route::post('/addresses', [AddressController::class, 'store']);

        Route::get('/addresses/{address}', [AddressController::class, 'show']);

        Route::patch('/addresses/{address}', [AddressController::class, 'update']);

        Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

        Route::patch('/addresses/{address}/default-shipping', [AddressController::class, 'setDefaultShipping']);

        Route::patch('/addresses/{address}/default-billing', [AddressController::class, 'setDefaultBilling']);

        Route::post('/checkout/preview', [CheckoutController::class, 'preview']);

        Route::post('/feeds/{feed}/like', [FeedController::class, 'like']);
        Route::delete('/feeds/{feed}/like', [FeedController::class, 'unlike']);
        Route::post('/feeds/{feed}/bookmark', [FeedController::class, 'bookmark']);
        Route::delete('/feeds/{feed}/bookmark', [FeedController::class, 'unbookmark']);
        Route::post('/feeds/{feed}/comments', [FeedController::class, 'storeComment']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
    });
});
