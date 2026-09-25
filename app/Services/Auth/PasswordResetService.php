<?php

namespace App\Services\Auth;

use App\Models\PasswordResetCode;
use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordResetService
{
    public const CODE_EXPIRY_MINUTES = 10;

    public const RESEND_COOLDOWN_SECONDS = 60;

    public const MAX_ATTEMPTS = 5;

    public function send(
        string $email,
    ): void {
        $email = strtolower(
            trim($email),
        );

        $user = User::query()
            ->where(
                'email',
                $email,
            )
            ->first();

        /*
         * Do not reveal whether an account exists.
         */

        if (! $user) {
            return;
        }

        $existingReset =
            PasswordResetCode::query()
                ->where(
                    'user_id',
                    $user->id,
                )
                ->first();

        /*
         * Silently respect the resend cooldown.
         *
         * Returning a different response here could reveal
         * whether the supplied email belongs to an account.
         */

        if (
            $existingReset?->last_sent_at &&
            $existingReset
                ->last_sent_at
                ->gt(
                    now()->subSeconds(
                        self::RESEND_COOLDOWN_SECONDS,
                    ),
                )
        ) {
            return;
        }

        /*
         * Generate a zero-padded 6-digit code.
         *
         * Examples:
         * 004281
         * 381729
         * 999999
         */

        $code = str_pad(
            (string) random_int(
                0,
                999999,
            ),
            6,
            '0',
            STR_PAD_LEFT,
        );

        PasswordResetCode::query()
            ->updateOrCreate(
                [
                    'user_id' =>
                        $user->id,
                ],
                [
                    'code_hash' =>
                        Hash::make(
                            $code,
                        ),

                    'expires_at' =>
                        now()->addMinutes(
                            self::CODE_EXPIRY_MINUTES,
                        ),

                    'attempts' =>
                        0,

                    'last_sent_at' =>
                        now(),
                ],
            );

        $user->notify(
            new PasswordResetCodeNotification(
                code: $code,
                expiresInMinutes:
                    self::CODE_EXPIRY_MINUTES,
            ),
        );
    }

    public function reset(
        string $email,
        string $code,
        string $password,
    ): bool {
        $email = strtolower(
            trim($email),
        );

        $user = User::query()
            ->where(
                'email',
                $email,
            )
            ->first();

        if (! $user) {
            return false;
        }

        $passwordReset =
            PasswordResetCode::query()
                ->where(
                    'user_id',
                    $user->id,
                )
                ->first();

        if (! $passwordReset) {
            return false;
        }

        /*
         * Expired code.
         */

        if (
            $passwordReset->isExpired()
        ) {
            $passwordReset->delete();

            return false;
        }

        /*
         * Too many failed attempts.
         */

        if (
            $passwordReset->attempts >=
            self::MAX_ATTEMPTS
        ) {
            return false;
        }

        /*
         * Incorrect code.
         */

        if (
            ! Hash::check(
                $code,
                $passwordReset->code_hash,
            )
        ) {
            $passwordReset->increment(
                'attempts',
            );

            return false;
        }

        /*
         * Reset password and revoke existing
         * Sanctum sessions.
         */

        DB::transaction(
            function () use (
                $user,
                $password,
                $passwordReset,
            ): void {
                $user->forceFill([
                    'password' =>
                        Hash::make(
                            $password,
                        ),
                ])->save();

                /*
                 * Require login again on all mobile devices
                 * after a successful password reset.
                 */

                $user->tokens()
                    ->delete();

                $passwordReset
                    ->delete();
            },
        );

        return true;
    }
}