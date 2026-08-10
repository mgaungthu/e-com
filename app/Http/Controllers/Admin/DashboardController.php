<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        Gate::authorize('dashboard.view');

        $salesTrend = collect(range(6, 0))->map(function (int $daysAgo): array {
            $date = today()->subDays($daysAgo);
            $orders = Order::query()->whereDate('created_at', $date);

            return [
                'date' => $date->toDateString(),
                'orders' => (clone $orders)->count(),
                'revenue' => (clone $orders)
                    ->where('status', OrderStatus::Delivered)
                    ->where('payment_status', PaymentStatus::Paid)
                    ->sum('grand_total'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_orders' => Order::query()->count(),
                    'today_orders' => Order::query()->whereDate('created_at', today())->count(),
                    'total_revenue' => Order::query()
                        ->where('status', OrderStatus::Delivered)
                        ->where('payment_status', PaymentStatus::Paid)
                        ->sum('grand_total'),
                    'total_customers' => User::query()->role('customer')->count(),
                    'total_products' => Product::query()->count(),
                    'low_stock_products' => Product::query()
                        ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                        ->count(),
                ],
                'order_statuses' => collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $status): array => [
                    $status->value => Order::query()->where('status', $status)->count(),
                ], ),
                'sales_trend' => $salesTrend,
                'recent_orders' => Order::query()
                    ->with('customer:id,name,display_name,email')
                    ->latest()
                    ->limit(5)
                    ->get(),
                'top_products' => OrderItem::query()
                    ->selectRaw('product_id, product_name, sku, SUM(quantity) as units_sold, SUM(line_total) as revenue')
                    ->whereHas('order', fn ($query) => $query
                        ->where('status', OrderStatus::Delivered)
                        ->where('payment_status', PaymentStatus::Paid))
                    ->groupBy('product_id', 'product_name', 'sku')
                    ->orderByDesc('units_sold')
                    ->limit(5)
                    ->get(),
            ],
        ]);
    }
}
