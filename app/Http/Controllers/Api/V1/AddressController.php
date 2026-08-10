<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Address\StoreAddressRequest;
use App\Http\Requests\Api\V1\Address\UpdateAddressRequest;
use App\Http\Resources\Api\V1\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = Address::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default_shipping')
            ->orderByDesc('is_default_billing')
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'addresses' => AddressResource::collection($addresses),
            ],
        ]);
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $address = DB::transaction(function () use ($request, $validated) {
            $userId = $request->user()->id;

            $hasExistingAddress = Address::query()
                ->where('user_id', $userId)
                ->exists();

            /*
             * ပထမဆုံး address ဆိုရင် shipping နဲ့ billing default
             * နှစ်ခုလုံးအဖြစ် အလိုအလျောက်သတ်မှတ်ပေးမယ်။
             */
            $makeDefaultShipping = ! $hasExistingAddress
                || ($validated['is_default_shipping'] ?? false);

            $makeDefaultBilling = ! $hasExistingAddress
                || ($validated['is_default_billing'] ?? false);

            if ($makeDefaultShipping) {
                Address::query()
                    ->where('user_id', $userId)
                    ->update([
                        'is_default_shipping' => false,
                    ]);
            }

            if ($makeDefaultBilling) {
                Address::query()
                    ->where('user_id', $userId)
                    ->update([
                        'is_default_billing' => false,
                    ]);
            }

            return Address::query()->create([
                ...$validated,
                'user_id' => $userId,
                'is_default_shipping' => $makeDefaultShipping,
                'is_default_billing' => $makeDefaultBilling,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully.',
            'data' => [
                'address' => new AddressResource($address),
            ],
        ], 201);
    }

    public function show(Request $request, Address $address): JsonResponse
    {
        $this->ensureAddressBelongsToUser($request, $address);

        return response()->json([
            'success' => true,
            'data' => [
                'address' => new AddressResource($address),
            ],
        ]);
    }

    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        $this->ensureAddressBelongsToUser($request, $address);

        $validated = $request->validated();

        DB::transaction(function () use ($request, $address, $validated) {
            $userId = $request->user()->id;

            if ($validated['is_default_shipping'] ?? false) {
                Address::query()
                    ->where('user_id', $userId)
                    ->whereKeyNot($address->id)
                    ->update([
                        'is_default_shipping' => false,
                    ]);
            }

            if ($validated['is_default_billing'] ?? false) {
                Address::query()
                    ->where('user_id', $userId)
                    ->whereKeyNot($address->id)
                    ->update([
                        'is_default_billing' => false,
                    ]);
            }

            $address->update($validated);
        });

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully.',
            'data' => [
                'address' => new AddressResource($address->fresh()),
            ],
        ]);
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->ensureAddressBelongsToUser($request, $address);

        DB::transaction(function () use ($request, $address) {
            $userId = $request->user()->id;

            $wasDefaultShipping = $address->is_default_shipping;
            $wasDefaultBilling = $address->is_default_billing;

            $address->delete();

            if ($wasDefaultShipping) {
                $nextShippingAddress = Address::query()
                    ->where('user_id', $userId)
                    ->latest('id')
                    ->first();

                $nextShippingAddress?->update([
                    'is_default_shipping' => true,
                ]);
            }

            if ($wasDefaultBilling) {
                $nextBillingAddress = Address::query()
                    ->where('user_id', $userId)
                    ->latest('id')
                    ->first();

                $nextBillingAddress?->update([
                    'is_default_billing' => true,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully.',
        ]);
    }

    public function setDefaultShipping(Request $request, Address $address): JsonResponse
    {
        $this->ensureAddressBelongsToUser($request, $address);

        DB::transaction(function () use ($request, $address) {
            Address::query()
                ->where('user_id', $request->user()->id)
                ->update([
                    'is_default_shipping' => false,
                ]);

            $address->update([
                'is_default_shipping' => true,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Default shipping address updated successfully.',
            'data' => [
                'address' => new AddressResource($address->fresh()),
            ],
        ]);
    }

    public function setDefaultBilling(Request $request, Address $address): JsonResponse
    {
        $this->ensureAddressBelongsToUser($request, $address);

        DB::transaction(function () use ($request, $address) {
            Address::query()
                ->where('user_id', $request->user()->id)
                ->update([
                    'is_default_billing' => false,
                ]);

            $address->update([
                'is_default_billing' => true,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Default billing address updated successfully.',
            'data' => [
                'address' => new AddressResource($address->fresh()),
            ],
        ]);
    }

    private function ensureAddressBelongsToUser(Request $request, Address $address): void
    {
        abort_unless($address->user_id === $request->user()->id, 404);
    }
}
