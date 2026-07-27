<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        $credentials = $request->safe()->only([
            'email',
            'password',
        ]);

        if (! Auth::attempt(
            $credentials,
            $request->boolean('remember'),
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Email or password is incorrect.',
                'errors' => [
                    'credentials' => [
                        'Email or password is incorrect.',
                    ],
                ],
            ], 422);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();

        if ($user->status !== 'active') {
            $message = match ($user->status) {
                'blocked' => 'Your account has been blocked.',
                'inactive' => 'Your account is currently inactive.',
                'pending' => 'Your account is waiting for approval.',
                default => 'Your account is not active.',
            };

            $this->logoutCurrentSession($request);

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => null,
            ], 403);
        }

        if (! $user->canAccessDashboard()) {
            $this->logoutCurrentSession($request);

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access the admin dashboard.',
                'errors' => null,
            ], 403);
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $this->userData($user),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (
            $user->status !== 'active' ||
            ! $user->canAccessDashboard()
        ) {
            $this->logoutCurrentSession($request);

            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to access the dashboard.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $this->userData($user),
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $this->logoutCurrentSession($request);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    private function logoutCurrentSession(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->full_name,
            'email' => $user->email,
            'avatar_path' => $user->avatar_path,

            'roles' => $user
                ->getRoleNames()
                ->values()
                ->all(),

            'permissions' => $user
                ->getAllPermissions()
                ->pluck('name')
                ->values()
                ->all(),
        ];
    }
}
