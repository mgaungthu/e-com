<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::view('/login', 'app')
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login.store');

Route::middleware('auth')->group(function () {
    Route::get('/admin/me', [AuthenticatedSessionController::class, 'me'])->name('admin.me');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Category routes
        |--------------------------------------------------------------------------
        */

        Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');

        Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');

        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('admin.categories.show');

        Route::post('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');

        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

        /*
        |--------------------------------------------------------------------------
        | Product routes
        |--------------------------------------------------------------------------
        */

        Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');

        Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');

        Route::get('/products/{product}', [ProductController::class, 'show'])->name('admin.products.show');

        Route::post('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');

        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

        /*
        |--------------------------------------------------------------------------
        | Inventory routes
        |--------------------------------------------------------------------------
        */

        Route::get('/inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');

        Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('admin.inventory.adjust');

        Route::get('/inventory/{product}/history', [InventoryController::class, 'history'])->name('admin.inventory.history');

        /*
        |--------------------------------------------------------------------------
        | Customer routes
        |--------------------------------------------------------------------------
        */

        Route::get('/customers', [CustomerController::class, 'index'])->name('admin.customers.index');

        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');

    });
});

/*
|--------------------------------------------------------------------------
| React SPA fallback
|--------------------------------------------------------------------------
|
| API-style Laravel routes must be declared before this route.
| All remaining URLs are handled by React Router.
|
*/

Route::view('/{path?}', 'app')
    ->where('path', '.*');
