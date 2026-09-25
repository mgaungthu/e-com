<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PasswordResetService $passwordResetService,
    ) {
    }

    public function forgot(
        ForgotPasswordRequest $request,
    ): JsonResponse {
        $this->passwordResetService->send(
            $request->validated(
                'email',
            ),
        );

        /*
         * Always return the same response regardless
         * of whether the email exists.
         */

        return $this->successResponse(
            data: [
                'expires_in_minutes' =>
                    PasswordResetService::CODE_EXPIRY_MINUTES,

                'resend_after_seconds' =>
                    PasswordResetService::RESEND_COOLDOWN_SECONDS,
            ],
            message:
                'If an account exists for this email, a password reset code has been sent.',
        );
    }

    public function reset(
        ResetPasswordRequest $request,
    ): JsonResponse {
        $validated =
            $request->validated();

        $resetSuccessful =
            $this->passwordResetService
                ->reset(
                    email:
                        $validated[
                            'email'
                        ],

                    code:
                        $validated[
                            'code'
                        ],

                    password:
                        $validated[
                            'password'
                        ],
                );

        if (! $resetSuccessful) {
            return $this->errorResponse(
                message:
                    'The password reset code is invalid or has expired.',
                status: 422,
                errors: [
                    'code' => [
                        'The password reset code is invalid or has expired.',
                    ],
                ],
            );
        }

        return $this->successResponse(
            message:
                'Password reset successfully. You can now sign in with your new password.',
        );
    }
}