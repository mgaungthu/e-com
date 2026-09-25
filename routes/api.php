<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AdminChatController;
use App\Http\Controllers\Api\V1\AdminQuickReplyController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\FeedController;
use App\Http\Controllers\Api\V1\HomeBannerController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PushDeviceController;
use Illuminate\Support\Facades\Route;

Route::domain(config('app.api_domain'))->group(function () {
    Route::prefix('v1')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Contact
        |--------------------------------------------------------------------------
        */

        Route::post('/contact', ContactController::class)->middleware('throttle:3,1');

        /*
        |--------------------------------------------------------------------------
        | Public Home Banners
        |--------------------------------------------------------------------------
        */

        Route::get('/home-banners', HomeBannerController::class);

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        Route::prefix('auth')
            ->group(function () {
                Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

                Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

                Route::post('/google', [AuthController::class, 'google'])->middleware('throttle:10,1');

                /*
                |--------------------------------------------------------------------------
                | Password Reset
                |--------------------------------------------------------------------------
                */

                Route::post('/password/forgot', [
                    PasswordResetController::class,
                    'forgot',
                ], )->middleware('throttle:3,1');

                Route::post('/password/reset', [
                    PasswordResetController::class,
                    'reset',
                ], )->middleware('throttle:10,1');

                Route::middleware('auth:sanctum')
                    ->group(function () {
                        Route::get('/me', [AuthController::class, 'me']);

                        Route::post('/email/verify', [
                            EmailVerificationController::class,
                            'verify',
                        ])->middleware('throttle:10,1');

                        Route::post('/email/resend', [
                            EmailVerificationController::class,
                            'resend',
                        ])->middleware('throttle:3,1');

                        Route::patch('/email', [
                            EmailVerificationController::class,
                            'updateEmail',
                        ])->middleware('throttle:5,1');

                        Route::post('/logout', [AuthController::class, 'logout']);

                        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
                    });
            });

        /*
        |--------------------------------------------------------------------------
        | Public Catalog
        |--------------------------------------------------------------------------
        */

        Route::get('/categories', [CategoryController::class, 'index']);

        Route::get('/categories/{slug}', [CategoryController::class, 'show']);

        Route::get('/products', [ProductController::class, 'index']);

        /*
         * Specific product routes must be declared
         * before /products/{slug}.
         */

        Route::get('/products/featured', [ProductController::class, 'featured']);

        Route::get('/products/new-arrivals', [ProductController::class, 'newArrivals']);

        Route::get('/products/promotions', [ProductController::class, 'promotions']);

        Route::get('/products/{slug}', [ProductController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        | Public Feeds
        |--------------------------------------------------------------------------
        */

        Route::get('/feeds', [FeedController::class, 'index']);

        Route::get('/feeds/{feed}', [FeedController::class, 'show']);

        Route::get('/feeds/{feed}/comments', [FeedController::class, 'comments']);

        /*
        |--------------------------------------------------------------------------
        | Public Payment Methods
        |--------------------------------------------------------------------------
        */

        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

        /*
        |--------------------------------------------------------------------------
        | Authenticated Routes
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'auth:sanctum',
            'verified.api',
        ])->group(function () {

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
            | Locations
            |--------------------------------------------------------------------------
            */

            Route::get('/locations/delivery-areas', [LocationController::class, 'deliveryAreas']);

            Route::get('/locations', [LocationController::class, 'index']);

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

            Route::patch('/addresses/{address}/default-shipping', [
                AddressController::class,
                'setDefaultShipping',
            ]);

            Route::patch('/addresses/{address}/default-billing', [
                AddressController::class,
                'setDefaultBilling',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Checkout
            |--------------------------------------------------------------------------
            */

            Route::post('/checkout/preview', [CheckoutController::class, 'preview']);

            /*
            |--------------------------------------------------------------------------
            | Feeds
            |--------------------------------------------------------------------------
            */

            Route::post('/feeds/{feed}/like', [FeedController::class, 'like']);

            Route::delete('/feeds/{feed}/like', [FeedController::class, 'unlike']);

            Route::post('/feeds/{feed}/bookmark', [FeedController::class, 'bookmark']);

            Route::delete('/feeds/{feed}/bookmark', [FeedController::class, 'unbookmark']);

            Route::post('/feeds/{feed}/comments', [FeedController::class, 'storeComment']);

            Route::post('/feeds', [FeedController::class, 'store']);

            Route::post('/feeds/{feed}', [FeedController::class, 'update']);

            Route::delete('/feeds/{feed}', [FeedController::class, 'destroy']);

            /*
            |--------------------------------------------------------------------------
            | Customer Chat
            |--------------------------------------------------------------------------
            */

            Route::prefix('chat')
                ->group(function () {
                    Route::get('/conversation', [ChatController::class, 'show']);

                    Route::get('/conversation/messages', [ChatController::class, 'messages']);

                    Route::post('/conversation/messages', [ChatController::class, 'storeMessage'])
                        ->middleware('throttle:30,1');

                    Route::post('/conversation/read', [ChatController::class, 'markAsRead']);
                });

            /*
            |--------------------------------------------------------------------------
            | Admin / Customer Support Chat
            |--------------------------------------------------------------------------
            |
            | These routes are consumed by the mobile app when the authenticated
            | user has chat.view / chat.reply permissions.
            |
            | Authorization is enforced inside the controllers using Gate.
            |
            */

            Route::prefix('chat/admin')
                ->group(function () {

                    /*
                    |--------------------------------------------------------------------------
                    | Conversations
                    |--------------------------------------------------------------------------
                    */

                    Route::controller(AdminChatController::class)
                        ->group(function () {
                            Route::get('/conversations', 'index');

                            Route::get('/conversations/{conversation}/messages', 'messages');

                            Route::post('/conversations/{conversation}/messages', 'storeMessage')
                                ->middleware('throttle:30,1');

                            Route::patch('/conversations/{conversation}/read', 'markAsRead');

                            /*
                            |--------------------------------------------------------------------------
                            | Star / Unstar
                            |--------------------------------------------------------------------------
                            |
                            | Star state is stored per authenticated admin.
                            |
                            */

                            Route::post('/conversations/{conversation}/star', 'star');

                            Route::delete('/conversations/{conversation}/star', 'unstar');
                        });

                    /*
                    |--------------------------------------------------------------------------
                    | Quick Replies
                    |--------------------------------------------------------------------------
                    */

                    Route::prefix('quick-replies')
                        ->controller(AdminQuickReplyController::class)
                        ->group(function () {
                            Route::get('/', 'index');

                            Route::post('/', 'store');

                            Route::patch('/{quickReply}', 'update');

                            Route::delete('/{quickReply}', 'destroy');
                        });
                });

            /*
            |--------------------------------------------------------------------------
            | Orders
            |--------------------------------------------------------------------------
            */

            Route::get('/orders', [OrderController::class, 'index']);

            Route::get('/orders/{order}', [OrderController::class, 'show']);

            Route::post('/orders', [OrderController::class, 'store']);

            /*
            |--------------------------------------------------------------------------
            | Push Devices
            |--------------------------------------------------------------------------
            */

            Route::prefix('push-devices')
                ->controller(PushDeviceController::class)
                ->group(function () {
                    Route::post('/', 'store');

                    Route::delete('/', 'destroy');
                });

            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */

            Route::prefix('notifications')
                ->controller(NotificationController::class)
                ->group(function () {
                    Route::get('/', 'index');

                    Route::get('/unread-count', 'unreadCount');

                    Route::patch('/read-all', 'markAllAsRead');

                    Route::patch('/{notification}/read', 'markAsRead');
                });
        });
    });
});
