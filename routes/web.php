<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerNoteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeedController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
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
        Route::get('/dashboard', DashboardController::class)->name('admin.dashboard');

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

        Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');

        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

        Route::get('/feeds', [FeedController::class, 'index'])->name('admin.feeds.index');
        Route::post('/feeds', [FeedController::class, 'store'])->name('admin.feeds.store');
        Route::get('/feeds/{feed}', [FeedController::class, 'show'])->name('admin.feeds.show');
        Route::patch('/feeds/{feed}', [FeedController::class, 'update'])->name('admin.feeds.update');
        Route::delete('/feeds/{feed}', [FeedController::class, 'destroy'])->name('admin.feeds.destroy');

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
        | Order routes
        |--------------------------------------------------------------------------
        */

        Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.status.update');
        Route::patch('/orders/{order}/payment', [OrderController::class, 'updatePayment'])->name('admin.orders.payment.update');

        Route::get('/staff', [StaffController::class, 'index'])->name('admin.staff.index');
        Route::post('/staff', [StaffController::class, 'store'])->name('admin.staff.store');
        Route::patch('/staff/{staff}', [StaffController::class, 'update'])->name('admin.staff.update');
        Route::get('/roles', [RoleController::class, 'index'])->name('admin.roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('admin.roles.store');
        Route::patch('/roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');
        Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('admin.settings.update');

        /*
        |--------------------------------------------------------------------------
        | Customer routes
        |--------------------------------------------------------------------------
        */

        Route::get('/customers', [CustomerController::class, 'index'])->name('admin.customers.index');

        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
        Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->name('admin.customers.update');
        Route::patch('/customers/{customer}/status', [CustomerController::class, 'updateStatus'])->name('admin.customers.status.update');
        Route::post('/customers/{customer}/notes', [CustomerNoteController::class, 'store'])->name('admin.customers.notes.store');
        Route::patch('/customers/{customer}/notes/{note}', [CustomerNoteController::class, 'update'])->name('admin.customers.notes.update');
        Route::delete('/customers/{customer}/notes/{note}', [CustomerNoteController::class, 'destroy'])->name('admin.customers.notes.destroy');

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
