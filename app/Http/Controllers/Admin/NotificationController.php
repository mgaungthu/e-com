<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Notification\SendNotificationRequest;
use App\Models\Product;
use App\Services\Notifications\CustomerNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    public function __construct(
        private readonly CustomerNotificationService $notificationService,
    ) {
    }

    public function products(Request $request): JsonResponse
    {
        Gate::authorize('notifications.view');

        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $search = trim(
            (string) ($validated['search'] ?? '')
        );

        $products = Product::query()
            ->select([
                'id',
                'name',
                'slug',
                'sku',
                'stock_quantity',
                'is_active',
            ])
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($productQuery) use ($search) {
                            $productQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'sku',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,

            'data' => [
                'products' => $products,
            ],
        ]);
    }

    public function send(
        SendNotificationRequest $request
    ): JsonResponse {
        Gate::authorize('notifications.send');

        $validated = $request->validated();

        $product = null;

        if (
            !empty(
                $validated['product_id']
            )
        ) {
            $product = Product::query()
                ->findOrFail(
                    $validated['product_id']
                );
        }

        $recipientCount =
            $this->notificationService->send(
                type: $validated['type'],
                title: $validated['title'],
                body: $validated['body'],
                product: $product,
            );

        return response()->json([
            'success' => true,

            'message' =>
                'Notification sent successfully.',

            'data' => [
                'notification' => [
                    'type' =>
                        $validated['type'],

                    'title' =>
                        $validated['title'],

                    'body' =>
                        $validated['body'],

                    'product' =>
                        $product
                            ? [
                                'id' =>
                                    $product->id,

                                'name' =>
                                    $product->name,

                                'slug' =>
                                    $product->slug,
                            ]
                            : null,

                    'recipient_count' =>
                        $recipientCount,
                ],
            ],
        ]);
    }
}