<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\UpdateEmailRequest;
use App\Http\Requests\Api\V1\Auth\VerifyEmailRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\EmailVerificationCode;
use App\Services\Auth\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmailVerificationController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly EmailVerificationService $emailVerificationService) {}

    public function verify(VerifyEmailRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(data: [
                'user' => new UserResource($user),
            ], message: 'Email is already verified.', );
        }

        $verification = EmailVerificationCode::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $verification) {
            return $this->errorResponse(message: 'No active verification code was found.', status: 422);
        }

        if ($verification->isExpired()) {
            $verification->delete();

            return $this->errorResponse(message: 'Verification code has expired. Please request a new code.', status: 422);
        }

        if ($verification->attempts >= 5) {
            return $this->errorResponse(message: 'Too many incorrect attempts. Please request a new verification code.', status: 429);
        }

        if (
            ! Hash::check($request->validated('code'), $verification->code_hash)
        ) {
            $verification->increment('attempts');

            if ($verification->fresh()->attempts >= 5) {
                return $this->errorResponse(message: 'Too many incorrect attempts. Please request a new verification code.', status: 429);
            }

            return $this->errorResponse(message: 'Verification code is incorrect.', status: 422, errors: [
                'code' => [
                    'Verification code is incorrect.',
                ],
            ], );
        }

        $user->markEmailAsVerified();

        $verification->delete();

        $user->refresh();

        return $this->successResponse(data: [
            'user' => new UserResource($user),
            'requires_email_verification' => false,
        ], message: 'Email verified successfully.', );
    }

    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(data: [
                'user' => new UserResource($user),
                'requires_email_verification' => false,
            ], message: 'Email is already verified.', );
        }

        $verification = EmailVerificationCode::query()
            ->where('user_id', $user->id)
            ->first();

        if (
            $verification?->last_sent_at &&
            $verification->last_sent_at->gt(now()->subSeconds(60))
        ) {
            $secondsRemaining = now()->diffInSeconds($verification->last_sent_at->copy()->addSeconds(60), false);

            return $this->errorResponse(message: "Please wait {$secondsRemaining} seconds before requesting another code.", status: 429);
        }

        $this->emailVerificationService->send($user);

        return $this->successResponse(data: [
            'requires_email_verification' => true,
        ], message: 'A new verification code has been sent.', );
    }

    public function updateEmail(UpdateEmailRequest $request): JsonResponse
    {
        $user = $request->user();

        $email = strtolower(trim($request->validated('email')));

        if ($user->email === $email) {
            return $this->successResponse(data: [
                'user' => new UserResource($user),
                'requires_email_verification' => ! $user->hasVerifiedEmail(),
            ], message: 'Email address is unchanged.', );
        }

        $user->forceFill([
            'email' => $email,
            'email_verified_at' => null,
        ])->save();

        $this
            ->emailVerificationService
            ->send($user);

        $user->refresh();

        return $this->successResponse(data: [
            'user' => new UserResource($user),
            'requires_email_verification' => true,
        ], message: 'Email updated. A new verification code has been sent.', );
    }
}
