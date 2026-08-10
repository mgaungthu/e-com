<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('reports.view');
        [$from, $to] = $this->dates($request);
        $sales = Order::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->where('status', OrderStatus::Delivered)
            ->where('payment_status', PaymentStatus::Paid);

        return response()->json([
            'success' => true,
            'data' => [
                'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
                'sales' => [
                    'orders' => (clone $sales)->count(),
                    'revenue' => (clone $sales)->sum('grand_total'),
                    'average_order_value' => (clone $sales)->avg('grand_total') ?? 0,
                    'discounts' => (clone $sales)->sum('discount_total'),
                    'shipping' => (clone $sales)->sum('shipping_total'),
                    'daily' => (clone $sales)->orderBy('created_at')->get(['created_at', 'grand_total']),
                ],
                'inventory' => Product::query()
                    ->with('category:id,name')
                    ->orderBy('stock_quantity')
                    ->get()
                    ->map(fn (Product $product): array => [
                        'id' => $product->id, 'name' => $product->name, 'sku' => $product->sku,
                        'category' => $product->category?->name,
                        'stock_quantity' => $product->stock_quantity,
                        'stock_status' => $product->stock_status,
                        'stock_value' => (float) $product->price * $product->stock_quantity,
                    ]),
                'customers' => CustomerProfile::query()
                    ->with('user:id,name,display_name,email')
                    ->orderByDesc('total_spent')
                    ->limit(20)
                    ->get(),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('reports.export');
        $validated = $request->validate(['type' => ['required', 'in:sales,inventory,customers']]);
        [$from, $to] = $this->dates($request);
        $type = $validated['type'];

        return response()->streamDownload(function () use ($type, $from, $to): void {
            $output = fopen('php://output', 'w');

            if ($type === 'sales') {
                fputcsv($output, ['Order', 'Date', 'Status', 'Payment', 'Total (MMK)']);
                Order::query()->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])->orderBy('created_at')->each(fn (Order $order) => fputcsv($output, [$order->order_number, $order->created_at, $order->status->value, $order->payment_status->value, $order->grand_total]));
            } elseif ($type === 'inventory') {
                fputcsv($output, ['Product', 'SKU', 'Quantity', 'Status', 'Unit Price (MMK)', 'Stock Value (MMK)']);
                Product::query()->orderBy('name')->each(fn (Product $product) => fputcsv($output, [$product->name, $product->sku, $product->stock_quantity, $product->stock_status, $product->price, (float) $product->price * $product->stock_quantity]));
            } else {
                fputcsv($output, ['Customer', 'Email', 'Total Orders', 'Total Spent (MMK)', 'Last Order']);
                CustomerProfile::query()->with('user')->orderByDesc('total_spent')->each(fn (CustomerProfile $profile) => fputcsv($output, [$profile->user?->full_name, $profile->user?->email, $profile->total_orders, $profile->total_spent, $profile->last_order_at]));
            }

            fclose($output);
        }, "{$type}-report-".now()->toDateString().'.csv', ['Content-Type' => 'text/csv']);
    }

    private function dates(Request $request): array
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return [
            $request->filled('date_from') ? $request->date('date_from') : today()->subDays(29),
            $request->filled('date_to') ? $request->date('date_to') : today(),
        ];
    }
}
