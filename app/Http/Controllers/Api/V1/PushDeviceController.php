<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PushDeviceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'push_token' => [
                'required',
                'string',
                'max:255',
            ],

            'device_id' => [
                'required',
                'string',
                'max:255',
            ],

            'platform' => [
                'required',
                'string',
                Rule::in([
                    'ios',
                    'android',
                ]),
            ],

            'device_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'device_model' => [
                'nullable',
                'string',
                'max:255',
            ],

            'os_version' => [
                'nullable',
                'string',
                'max:100',
            ],

            'app_version' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Device
        |--------------------------------------------------------------------------
        |
        | device_id identifies the installation/device.
        |
        | If another account logs into the same device,
        | ownership is transferred to the current user.
        |
        */

        $device = UserDevice::query()->updateOrCreate(
            [
                'device_id' =>
                    $validated['device_id'],
            ],
            [
                'user_id' =>
                    $request->user()->id,

                'platform' =>
                    $validated['platform'],

                'device_name' =>
                    $validated['device_name'] ?? null,

                'device_model' =>
                    $validated['device_model'] ?? null,

                'os_version' =>
                    $validated['os_version'] ?? null,

                'app_version' =>
                    $validated['app_version'] ?? null,

                'push_token' =>
                    $validated['push_token'],

                'last_active_at' =>
                    now(),

                'is_active' =>
                    true,
            ],
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Push notification device registered.',

            'data' => [
                'device' => [
                    'id' =>
                        $device->id,

                    'device_id' =>
                        $device->device_id,

                    'platform' =>
                        $device->platform,

                    'last_active_at' =>
                        $device->last_active_at
                            ?->toISOString(),
                ],
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
{
    $validated = $request->validate([
        'device_id' => [
            'required',
            'string',
            'max:255',
        ],
    ]);

    $updated = UserDevice::query()
        ->where('user_id', $request->user()->id)
        ->where('device_id', $validated['device_id'])
        ->update([
            'push_token' => null,
            'is_active' => false,
            'last_active_at' => now(),
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Push device unregistered.',
        'data' => [
            'user_id' => $request->user()->id,
            'device_id' => $validated['device_id'],
            'updated' => $updated,
        ],
    ]);
}
}