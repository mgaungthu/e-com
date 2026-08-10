<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = DB::transaction(function () use ($validated) {
            $user = User::query()->create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'display_name' => trim($validated['first_name'].' '.($validated['last_name'] ?? '')),
                'name' => trim($validated['first_name'].' '.($validated['last_name'] ?? '')),
                'email' => strtolower($validated['email']),
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'role' => 'customer',
                'status' => 'active',
            ]);

            $deviceName = $validated['device_name'] ?? 'mobile-app';

            $token = $user
                ->createToken($deviceName, ['customer'])
                ->plainTextToken;

            return [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ];
        });

        return $this->successResponse(data: $result, message: 'Account created successfully.', status: 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()
            ->where('email', strtolower($validated['email']))
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->errorResponse(message: 'Email or password is incorrect.', status: 422, errors: [
                'email' => [
                    'Email or password is incorrect.',
                ],
            ], );
        }

        if ($user->status !== 'active') {
            return $this->errorResponse(message: match ($user->status) {
                'blocked' => 'Your account has been blocked.',
                'inactive' => 'Your account is inactive.',
                'pending' => 'Your account is pending approval.',
                default => 'Your account is unavailable.',
            }, status: 403, );
        }

        $deviceName = $validated['device_name'] ?? 'mobile-app';

        $token = $user
            ->createToken($deviceName, ['customer'])
            ->plainTextToken;

        return $this->successResponse(data: [
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], message: 'Login successful.', );
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(data: [
            'user' => new UserResource($request->user()),
        ], );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        return $this->successResponse(message: 'Logged out successfully.');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(message: 'Logged out from all devices successfully.');
    }
}
