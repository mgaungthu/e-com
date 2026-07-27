<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InventoryTransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\AdjustInventoryRequest;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with('category:id,name')
            ->latest();

        $search = trim(
            (string) $request->input('search'),
        );

        if ($search !== '') {
            $query->where(function ($productQuery) use (
                $search,
            ) {
                $productQuery
                    ->where(
                        'name',
                        'like',
                        "%{$search}%",
                    )
                    ->orWhere(
                        'sku',
                        'like',
                        "%{$search}%",
                    )
                    ->orWhere(
                        'barcode',
                        'like',
                        "%{$search}%",
                    );
            });
        }

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->integer('category_id'),
            );
        }

        if ($request->filled('stock_status')) {
            match (
                $request
                    ->string('stock_status')
                    ->toString()
            ) {
                'in_stock' => $query
                    ->whereColumn(
                        'stock_quantity',
                        '>',
                        'low_stock_threshold',
                    ),

                'low_stock' => $query
                    ->where(
                        'stock_quantity',
                        '>',
                        0,
                    )
                    ->whereColumn(
                        'stock_quantity',
                        '<=',
                        'low_stock_threshold',
                    ),

                'out_of_stock' => $query
                    ->where(
                        'stock_quantity',
                        '<=',
                        0,
                    ),

                default => null,
            };
        }

        $perPage = min(
            max(
                $request->integer(
                    'per_page',
                    15,
                ),
                1,
            ),
            100,
        );

        return response()->json([
            'success' => true,
            'data' => $query->paginate($perPage),
        ]);
    }

    public function adjust(
        AdjustInventoryRequest $request,
        Product $product,
    ): JsonResponse {
        $validated = $request->validated();

        try {
            $transaction =
                $this->inventoryService->adjust(
                    product: $product,
                    type: InventoryTransactionType::from(
                        $validated['type'],
                    ),
                    quantity: $validated['quantity'],
                    user: $request->user(),
                    reason: $validated['reason'] ?? null,
                    note: $validated['note'] ?? null,
                );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'quantity' => [
                        $exception->getMessage(),
                    ],
                ],
            ], 422);
        }

        $product->refresh();
        $product->load('category:id,name');

        return response()->json([
            'success' => true,
            'message' =>
                'Inventory updated successfully.',
            'data' => [
                'product' => $product,
                'transaction' => $transaction->load(
                    'user:id,name',
                ),
            ],
        ]);
    }

    public function history(
        Request $request,
        Product $product,
    ): JsonResponse {
        $perPage = min(
            max(
                $request->integer(
                    'per_page',
                    15,
                ),
                1,
            ),
            100,
        );

        $transactions =
            InventoryTransaction::query()
                ->where(
                    'product_id',
                    $product->id,
                )
                ->with('user:id,name')
                ->latest()
                ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }
}