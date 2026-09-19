<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\Admin\PaymentMethod\UpdatePaymentMethodRequest;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): JsonResponse {
        Gate::authorize(
            'payment_methods.view'
        );

        $query = PaymentMethod::query()
            ->orderBy('sort_order')
            ->orderBy('id');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input('search')
        );

        if ($search !== '') {
            $query->where(
                function ($paymentQuery) use ($search) {
                    $paymentQuery
                        ->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'code',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'account_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'account_number',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Type
        |--------------------------------------------------------------------------
        */

        if ($request->filled('type')) {
            match (
                $request
                    ->string('type')
                    ->toString()
            ) {
                'cod' =>
                    $query->where(
                        'type',
                        'cod'
                    ),

                'ewallet' =>
                    $query->where(
                        'type',
                        'ewallet'
                    ),

                default => null,
            };
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            match (
                $request
                    ->string('status')
                    ->toString()
            ) {
                'active' =>
                    $query->where(
                        'is_active',
                        true
                    ),

                'inactive' =>
                    $query->where(
                        'is_active',
                        false
                    ),

                default => null,
            };
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            max(
                $request->integer(
                    'per_page',
                    15
                ),
                1
            ),
            100
        );

        return response()->json([
            'success' => true,

            'data' =>
                $query->paginate(
                    $perPage
                ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StorePaymentMethodRequest $request
    ): JsonResponse {
        $data =
            $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Boolean Fields
        |--------------------------------------------------------------------------
        */

        $data['requires_proof'] =
            $request->boolean(
                'requires_proof'
            );

        $data['is_active'] =
            $request->boolean(
                'is_active'
            );

        /*
        |--------------------------------------------------------------------------
        | Uploaded Files
        |--------------------------------------------------------------------------
        */

        unset(
            $data['logo'],
            $data['qr_image'],
            $data['remove_logo'],
            $data['remove_qr_image'],
        );

        $logoPath = null;
        $qrImagePath = null;

        try {
            if ($request->hasFile('logo')) {
                $logoPath = $request
                    ->file('logo')
                    ->store(
                        'payment-methods/logos',
                        'public'
                    );
            }

            if (
                $request->hasFile(
                    'qr_image'
                )
            ) {
                $qrImagePath = $request
                    ->file('qr_image')
                    ->store(
                        'payment-methods/qr',
                        'public'
                    );
            }

            $paymentMethod =
                DB::transaction(
                    function () use (
                        $data,
                        $logoPath,
                        $qrImagePath,
                    ): PaymentMethod {
                        return PaymentMethod::query()
                            ->create([
                                ...$data,

                                'logo_path' =>
                                    $logoPath,

                                'qr_image_path' =>
                                    $qrImagePath,
                            ]);
                    }
                );
        } catch (\Throwable $exception) {
            if ($logoPath !== null) {
                Storage::disk('public')
                    ->delete($logoPath);
            }

            if ($qrImagePath !== null) {
                Storage::disk('public')
                    ->delete($qrImagePath);
            }

            throw $exception;
        }

        return response()->json([
            'success' => true,

            'message' =>
                'Payment method created successfully.',

            'data' => [
                'payment_method' =>
                    $this->serializePaymentMethod(
                        $paymentMethod
                    ),
            ],
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        PaymentMethod $paymentMethod
    ): JsonResponse {
        Gate::authorize(
            'payment_methods.view'
        );

        return response()->json([
            'success' => true,

            'data' => [
                'payment_method' =>
                    $this->serializePaymentMethod(
                        $paymentMethod
                    ),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdatePaymentMethodRequest $request,
        PaymentMethod $paymentMethod
    ): JsonResponse {
        $data =
            $request->validated();

        $data['requires_proof'] =
            $request->boolean(
                'requires_proof'
            );

        $data['is_active'] =
            $request->boolean(
                'is_active'
            );

        $removeLogo =
            $request->boolean(
                'remove_logo'
            );

        $removeQrImage =
            $request->boolean(
                'remove_qr_image'
            );

        unset(
            $data['logo'],
            $data['qr_image'],
            $data['remove_logo'],
            $data['remove_qr_image'],
        );

        /*
        |--------------------------------------------------------------------------
        | Current Paths
        |--------------------------------------------------------------------------
        */

        $oldLogoPath =
            $paymentMethod->logo_path;

        $oldQrImagePath =
            $paymentMethod->qr_image_path;

        /*
        |--------------------------------------------------------------------------
        | New Uploads
        |--------------------------------------------------------------------------
        */

        $newLogoPath = null;
        $newQrImagePath = null;

        try {
            if ($request->hasFile('logo')) {
                $newLogoPath = $request
                    ->file('logo')
                    ->store(
                        'payment-methods/logos',
                        'public'
                    );
            }

            if (
                $request->hasFile(
                    'qr_image'
                )
            ) {
                $newQrImagePath = $request
                    ->file('qr_image')
                    ->store(
                        'payment-methods/qr',
                        'public'
                    );
            }

            DB::transaction(
                function () use (
                    $paymentMethod,
                    $data,
                    $removeLogo,
                    $removeQrImage,
                    $newLogoPath,
                    $newQrImagePath,
                ): void {
                    /*
                    |--------------------------------------------------------------------------
                    | Logo
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $newLogoPath !== null
                    ) {
                        $data['logo_path'] =
                            $newLogoPath;
                    } elseif ($removeLogo) {
                        $data['logo_path'] =
                            null;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | QR Image
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $newQrImagePath !== null
                    ) {
                        $data['qr_image_path'] =
                            $newQrImagePath;
                    } elseif ($removeQrImage) {
                        $data['qr_image_path'] =
                            null;
                    }

                    $paymentMethod->update(
                        $data
                    );
                }
            );
        } catch (\Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Delete Newly Uploaded Orphan Files
            |--------------------------------------------------------------------------
            */

            if ($newLogoPath !== null) {
                Storage::disk('public')
                    ->delete(
                        $newLogoPath
                    );
            }

            if (
                $newQrImagePath !== null
            ) {
                Storage::disk('public')
                    ->delete(
                        $newQrImagePath
                    );
            }

            throw $exception;
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Replaced / Removed Old Files
        |--------------------------------------------------------------------------
        |
        | Do this only after the database transaction succeeds.
        |--------------------------------------------------------------------------
        */

        if (
            $oldLogoPath !== null &&
            (
                $newLogoPath !== null ||
                $removeLogo
            )
        ) {
            Storage::disk('public')
                ->delete($oldLogoPath);
        }

        if (
            $oldQrImagePath !== null &&
            (
                $newQrImagePath !== null ||
                $removeQrImage
            )
        ) {
            Storage::disk('public')
                ->delete(
                    $oldQrImagePath
                );
        }

        $paymentMethod->refresh();

        return response()->json([
            'success' => true,

            'message' =>
                'Payment method updated successfully.',

            'data' => [
                'payment_method' =>
                    $this->serializePaymentMethod(
                        $paymentMethod
                    ),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        PaymentMethod $paymentMethod
    ): JsonResponse {
        Gate::authorize(
            'payment_methods.delete'
        );

        /*
        |--------------------------------------------------------------------------
        | Preserve Payment History
        |--------------------------------------------------------------------------
        |
        | Do not delete payment methods that are already referenced by an
        | order payment. Admin can deactivate them instead.
        |--------------------------------------------------------------------------
        */

        if (
            $paymentMethod
                ->orderPayments()
                ->exists()
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'This payment method has existing payment history and cannot be deleted. Deactivate it instead.',
            ], 422);
        }

        $logoPath =
            $paymentMethod->logo_path;

        $qrImagePath =
            $paymentMethod->qr_image_path;

        $paymentMethod->delete();

        if ($logoPath !== null) {
            Storage::disk('public')
                ->delete($logoPath);
        }

        if ($qrImagePath !== null) {
            Storage::disk('public')
                ->delete($qrImagePath);
        }

        return response()->json([
            'success' => true,

            'message' =>
                'Payment method deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Serialize Payment Method
    |--------------------------------------------------------------------------
    */

    private function serializePaymentMethod(
        PaymentMethod $paymentMethod
    ): array {
        return [
            'id' =>
                $paymentMethod->id,

            'name' =>
                $paymentMethod->name,

            'code' =>
                $paymentMethod->code,

            'type' =>
                $paymentMethod->type,

            'account_name' =>
                $paymentMethod->account_name,

            'account_number' =>
                $paymentMethod->account_number,

            'instructions' =>
                $paymentMethod->instructions,

            'requires_proof' =>
                $paymentMethod->requires_proof,

            'is_active' =>
                $paymentMethod->is_active,

            'sort_order' =>
                $paymentMethod->sort_order,

            'logo_url' =>
                $paymentMethod->logo_path
                    ? Storage::disk('public')
                        ->url(
                            $paymentMethod->logo_path
                        )
                    : null,

            'qr_image_url' =>
                $paymentMethod->qr_image_path
                    ? Storage::disk('public')
                        ->url(
                            $paymentMethod->qr_image_path
                        )
                    : null,

            'created_at' =>
                $paymentMethod->created_at
                    ?->toISOString(),

            'updated_at' =>
                $paymentMethod->updated_at
                    ?->toISOString(),
        ];
    }
}