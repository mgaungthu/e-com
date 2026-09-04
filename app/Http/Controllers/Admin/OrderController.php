<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderPaymentConfirmed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\UpdateOrderPaymentRequest;
use App\Http\Requests\Admin\Order\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    /*
    |--------------------------------------------------------------------------
    | Order List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('orders.view');

        $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                Rule::enum(OrderStatus::class),
            ],

            'payment_status' => [
                'nullable',
                Rule::enum(PaymentStatus::class),
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $query = Order::query()
            ->with('customer:id,name,first_name,last_name,display_name,email')
            ->withCount('items')
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $search = trim((string) $request->input('search'));

        if ($search !== '') {
            $query->where(function ($orderQuery) use ($search) {
                $orderQuery
                    ->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('display_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Order Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request
                ->string('status')
                ->toString());
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request
                ->string('payment_status')
                ->toString());
        }

        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return response()->json([
            'success' => true,

            'data' => $query->paginate($perPage),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Order Detail
    |--------------------------------------------------------------------------
    */

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('orders.view');

        return response()->json([
            'success' => true,

            'data' => [
                'order' => $this->loadOrder($order),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Order Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        try {
            $updatedOrder =
                $this->orderService
                    ->transitionStatus($order, OrderStatus::from($request->validated('status')), $request->user(), $request->validated('note'));
        } catch (
            DomainException|
            \InvalidArgumentException $exception
        ) {
            return response()->json([
                'success' => false,

                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,

            'message' => 'Order status updated successfully.',

            'data' => [
                'order' => $this->loadOrder($updatedOrder),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Payment Status
    |--------------------------------------------------------------------------
    */

    public function updatePayment(UpdateOrderPaymentRequest $request, Order $order): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Current Payment Status
        |--------------------------------------------------------------------------
        */

        $previousPaymentStatus = $order->payment_status;

        $newPaymentStatus = PaymentStatus::from($request->validated('payment_status'));

        logger()->info('Payment transition requested', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'previous_payment_status' => $previousPaymentStatus->value,
            'requested_payment_status' => $newPaymentStatus->value,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Transition
        |--------------------------------------------------------------------------
        */

        try {
            $updatedOrder = $this->orderService->transitionPayment($order, $newPaymentStatus);
        } catch (DomainException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Refresh Order
        |--------------------------------------------------------------------------
        |
        | Make sure we are comparing against the actual database state.
        |--------------------------------------------------------------------------
        */

        $updatedOrder->refresh();

        logger()->info('Payment transition completed', [
            'order_id' => $updatedOrder->id,
            'previous_payment_status' => $previousPaymentStatus->value,
            'current_payment_status' => $updatedOrder->payment_status->value,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Payment Confirmation
        |--------------------------------------------------------------------------
        */

        if (
            $previousPaymentStatus !== PaymentStatus::Paid
            && $updatedOrder->payment_status === PaymentStatus::Paid
        ) {
            logger()->info('Dispatching OrderPaymentConfirmed', [
                'order_id' => $updatedOrder->id,
                'order_number' => $updatedOrder->order_number,
            ]);

            try {
                OrderPaymentConfirmed::dispatch($updatedOrder);

                logger()->info('OrderPaymentConfirmed dispatched', [
                    'order_id' => $updatedOrder->id,
                ]);
            } catch (\Throwable $exception) {
                logger()->error('OrderPaymentConfirmed dispatch failed', [
                    'order_id' => $updatedOrder->id,
                    'message' => $exception->getMessage(),
                ]);

                report($exception);
            }
        } else {
            logger()->info('Payment confirmation event not required', [
                'order_id' => $updatedOrder->id,
                'previous_payment_status' => $previousPaymentStatus->value,
                'current_payment_status' => $updatedOrder->payment_status->value,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully.',
            'data' => [
                'order' => $this->loadOrder($updatedOrder),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Load Order Detail Relations
    |--------------------------------------------------------------------------
    */

    private function loadOrder(Order $order): Order
    {
        return $order->load([
            'customer:id,name,first_name,last_name,display_name,email,phone',

            'items.product.primaryImage',

            'statusHistories.user:id,name,first_name,last_name,display_name',

            'latestPayment.paymentMethod',
        ]);
    }
}
