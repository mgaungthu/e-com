<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\Auth\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly EmailVerificationService $emailVerificationService,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = DB::transaction(function () use ($validated) {
            $user = User::query()->create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'display_name' => trim(
                    $validated['first_name'].' '.($validated['last_name'] ?? '')
                ),
                'name' => trim(
                    $validated['first_name'].' '.($validated['last_name'] ?? '')
                ),
                'email' => strtolower($validated['email']),
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'status' => 'active',
            ]);

            $user->assignRole('customer');

            $deviceName = $validated['device_name'] ?? 'mobile-app';

            $token = $user
                ->createToken($deviceName, ['customer'])
                ->plainTextToken;

            return [
                'user_model' => $user,
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'requires_email_verification' => ! $user->hasVerifiedEmail(),
            ];
        });

        /*
         * Send verification email only after
         * the transaction has committed successfully.
         */
        $this->emailVerificationService->send(
            $result['user_model'],
        );

        unset($result['user_model']);

        return $this->successResponse(
            data: $result,
            message: 'Account created successfully. Please verify your email.',
            status: 201,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()
            ->where('email', strtolower($validated['email']))
            ->first();

        if (
            ! $user ||
            ! Hash::check(
                $validated['password'],
                $user->password,
            )
        ) {
            return $this->errorResponse(
                message: 'Email or password is incorrect.',
                status: 422,
            );
        }

        if ($user->status !== 'active') {
            return $this->errorResponse(
                message: match ($user->status) {
                    'blocked' => 'Your account has been blocked.',
                    'inactive' => 'Your account is inactive.',
                    'pending' => 'Your account is pending approval.',
                    default => 'Your account is unavailable.',
                },
                status: 403,
            );
        }

        $deviceName = $validated['device_name'] ?? 'mobile-app';

        $token = $user
            ->createToken($deviceName, ['customer'])
            ->plainTextToken;

        return $this->successResponse(
            data: [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'requires_email_verification' => ! $user->hasVerifiedEmail(),
            ],
            message: $user->hasVerifiedEmail()
                ? 'Login successful.'
                : 'Login successful. Please verify your email.',
        );
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse(data: [
            'user' => new UserResource($user),
            'requires_email_verification' => ! $user->hasVerifiedEmail(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        return $this->successResponse(
            message: 'Logged out successfully.',
        );
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(
            message: 'Logged out from all devices successfully.',
        );
    }
}