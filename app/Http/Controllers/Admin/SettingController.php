<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('settings.manage');

        return response()->json([
            'success' => true,
            'data' => Setting::query()->orderBy('group')->orderBy('key')->get()->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting->typedValue()]),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        Gate::authorize('settings.manage');
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'currency' => ['required', Rule::in(['MMK'])],
            'timezone' => ['required', 'timezone'],
            'order_prefix' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/'],
            'default_low_stock_threshold' => ['required', 'integer', 'min:0', 'max:1000000'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'shipping_fee' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::query()->where('key', $key)->update(['value' => (string) ($value ?? '')]);
        }

        Cache::forget('app.settings');

        return response()->json(['success' => true, 'message' => 'Settings updated successfully.', 'data' => $validated]);
    }
}
