<?php

namespace App\Services\Auth;

use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\VerifyEmailCodeNotification;
use Illuminate\Support\Facades\Hash;

class EmailVerificationService
{
    private const CODE_EXPIRY_MINUTES = 10;

    public function send(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        /*
         * Generate a zero-padded 4-digit code.
         *
         * Examples:
         * 0042
         * 3817
         * 9999
         */
        $code = str_pad(
            (string) random_int(0, 9999),
            4,
            '0',
            STR_PAD_LEFT,
        );

        EmailVerificationCode::query()->updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(
                    self::CODE_EXPIRY_MINUTES,
                ),
                'attempts' => 0,
                'last_sent_at' => now(),
            ],
        );

        $user->notify(
            new VerifyEmailCodeNotification(
                code: $code,
                expiresInMinutes: self::CODE_EXPIRY_MINUTES,
            ),
        );
    }
}