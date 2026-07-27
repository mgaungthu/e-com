<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->can('customers.view'),
            403,
        );

        $query = User::query()
            ->role('customer')
            ->with([
                'customerProfile:user_id,total_orders,total_spent,loyalty_points,last_order_at',
            ])
            ->withCount('addresses')
            ->latest();

        $search = trim(
            (string) $request->input('search'),
        );

        if ($search !== '') {
            $query->where(function ($customerQuery) use ($search) {
                $customerQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            match (
                $request
                    ->string('status')
                    ->toString()
            ) {
                'active' => $query->where(
                    'status',
                    'active',
                ),

                'inactive' => $query->where(
                    'status',
                    'inactive',
                ),

                'blocked' => $query->where(
                    'status',
                    'blocked',
                ),

                'pending' => $query->where(
                    'status',
                    'pending',
                ),

                default => null,
            };
        }

        if ($request->filled('order_status')) {
            match (
                $request
                    ->string('order_status')
                    ->toString()
            ) {
                'has_orders' => $query->whereHas(
                    'customerProfile',
                    fn ($profileQuery) => $profileQuery
                        ->where('total_orders', '>', 0),
                ),

                'no_orders' => $query->where(
                    fn ($customerQuery) => $customerQuery
                        ->whereDoesntHave('customerProfile')
                        ->orWhereHas(
                            'customerProfile',
                            fn ($profileQuery) => $profileQuery
                                ->where('total_orders', 0),
                        ),
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

    public function show(
        Request $request,
        User $customer,
    ): JsonResponse {
        abort_unless(
            $request->user()?->can('customers.view'),
            403,
        );

        abort_unless(
            $customer->hasRole('customer'),
            404,
        );

        $customer->load([
            'customerProfile',
            'addresses' => fn ($query) => $query
                ->orderByDesc('is_default_shipping')
                ->orderByDesc('is_default_billing')
                ->latest(),

            'devices' => fn ($query) => $query
                ->latest('updated_at'),

            'preference',

            'customerNotes' => fn ($query) => $query
                ->with('creator:id,name,first_name,last_name,display_name')
                ->orderByDesc('is_pinned')
                ->latest(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
            ],
        ]);
    }
}