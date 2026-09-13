<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\GoogleLoginRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\Auth\EmailVerificationService;
use App\Services\Auth\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly EmailVerificationService $emailVerificationService,
        private readonly GoogleAuthService $googleAuthService,
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
            ->where(
                'email',
                strtolower($validated['email']),
            )
            ->first();

        if (
            ! $user
            || ! Hash::check(
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
                message: $this->accountStatusMessage(
                    $user->status,
                ),
                status: 403,
            );
        }

        /*
         * Keep login metadata consistent across
         * email/password and Google authentication.
         */
        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $deviceName = $validated['device_name'] ?? 'mobile-app';

        $token = $user
            ->createToken(
                $deviceName,
                ['customer'],
            )
            ->plainTextToken;

        $user = $user->fresh();

        return $this->successResponse(
            data: [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'requires_email_verification' =>
                    ! $user->hasVerifiedEmail(),
            ],
            message: $user->hasVerifiedEmail()
                ? 'Login successful.'
                : 'Login successful. Please verify your email.',
        );
    }

    public function google(
        GoogleLoginRequest $request,
    ): JsonResponse {
        $validated = $request->validated();

        /*
         * Verify the Google ID token on the backend.
         *
         * Never trust email, name, Google ID, or avatar
         * values supplied directly by the mobile client.
         */
        try {
            $payload = $this->googleAuthService
                ->verifyIdToken(
                    $validated['id_token'],
                );
        } catch (RuntimeException $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Unable to authenticate with Google.',
                status: 422,
            );
        }

        $googleId = (string) $payload['sub'];

        $email = strtolower(
            trim(
                (string) $payload['email'],
            ),
        );

        $firstName = trim(
            (string) ($payload['given_name'] ?? ''),
        );

        $lastName = trim(
            (string) ($payload['family_name'] ?? ''),
        );

        $fullName = trim(
            (string) ($payload['name'] ?? ''),
        );

        if ($fullName === '') {
            $fullName = trim(
                $firstName.' '.$lastName,
            );
        }

        if ($fullName === '') {
            $fullName = $email;
        }

        $picture = isset($payload['picture'])
            ? trim((string) $payload['picture'])
            : null;

        try {
            $user = DB::transaction(function () use (
                $googleId,
                $email,
                $firstName,
                $lastName,
                $fullName,
                $picture,
            ) {
                /*
                 * First, look for an account that has already
                 * been linked to this Google account.
                 */
                $user = User::query()
                    ->where('google_id', $googleId)
                    ->lockForUpdate()
                    ->first();

                /*
                 * If no Google link exists yet, try to find
                 * an existing BSC account using the verified
                 * Google email address.
                 */
                if (! $user) {
                    $user = User::query()
                        ->where('email', $email)
                        ->lockForUpdate()
                        ->first();
                }

                /*
                 * Existing BSC account.
                 */
                if ($user) {
                    /*
                     * Important:
                     *
                     * Reject unavailable accounts before making
                     * any Google-link, email verification, or
                     * avatar changes.
                     */
                    if ($user->status !== 'active') {
                        throw new RuntimeException(
                            $this->accountStatusMessage(
                                $user->status,
                            ),
                            403,
                        );
                    }

                    /*
                     * Do not allow a different Google account
                     * to replace an existing Google link.
                     */
                    if (
                        $user->google_id !== null
                        && $user->google_id !== $googleId
                    ) {
                        throw new RuntimeException(
                            'This email is already linked to another Google account.',
                            409,
                        );
                    }

                    $updates = [
                        'google_id' => $googleId,
                    ];

                    /*
                     * Keep the latest Google profile image.
                     *
                     * User::avatar_url can still prioritize a
                     * manually uploaded avatar_path.
                     */
                    if (
                        $picture !== null
                        && $picture !== ''
                    ) {
                        $updates['google_avatar_url'] = $picture;
                    }

                    /*
                     * Google has verified ownership of this
                     * email address, so an existing unverified
                     * BSC account can now be marked verified.
                     */
                    if (! $user->hasVerifiedEmail()) {
                        $updates['email_verified_at'] = now();
                    }

                    $user
                        ->forceFill($updates)
                        ->save();

                    return $user->fresh();
                }

                /*
                 * Completely new Google user.
                 *
                 * Phone remains null because Google Sign-In
                 * does not provide the application's required
                 * delivery phone number.
                 *
                 * The current users.password column is
                 * non-nullable, so use a random internal
                 * password that the user never sees.
                 */
                $user = User::query()->create([
                    'first_name' => $firstName !== ''
                        ? $firstName
                        : null,

                    'last_name' => $lastName !== ''
                        ? $lastName
                        : null,

                    'display_name' => $fullName,
                    'name' => $fullName,

                    'email' => $email,
                    'phone' => null,

                    'password' => Hash::make(
                        Str::random(64),
                    ),

                    'google_id' => $googleId,

                    'google_avatar_url' =>
                        $picture !== null
                        && $picture !== ''
                            ? $picture
                            : null,

                    /*
                     * Google has already verified the email.
                     */
                    'email_verified_at' => now(),

                    'status' => 'active',
                ]);

                /*
                 * Keep the same role convention as normal
                 * customer registration.
                 */
                $user->assignRole('customer');

                return $user->fresh();
            });
        } catch (RuntimeException $exception) {
            report($exception);

            /*
             * RuntimeException is used above for expected
             * business/authentication conflicts.
             */
            $status = in_array(
                $exception->getCode(),
                [403, 409],
                true,
            )
                ? $exception->getCode()
                : 409;

            return $this->errorResponse(
                message: $exception->getMessage(),
                status: $status,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->errorResponse(
                message: 'Unable to authenticate with Google.',
                status: 500,
            );
        }

        /*
         * Defense-in-depth status check.
         *
         * Normally this has already been checked inside
         * the transaction for an existing account.
         */
        if ($user->status !== 'active') {
            return $this->errorResponse(
                message: $this->accountStatusMessage(
                    $user->status,
                ),
                status: 403,
            );
        }

        /*
         * Record login metadata before returning
         * the authenticated session.
         */
        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $deviceName =
            $validated['device_name']
            ?? 'mobile-app';

        /*
         * Return the same Sanctum session structure
         * used by regular email/password login.
         */
        $token = $user
            ->createToken(
                $deviceName,
                ['customer'],
            )
            ->plainTextToken;

        $user = $user->fresh();

        return $this->successResponse(
            data: [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',

                /*
                 * This should always be false for a valid
                 * Google login, but keeping it preserves the
                 * existing auth response contract.
                 */
                'requires_email_verification' =>
                    ! $user->hasVerifiedEmail(),
            ],
            message: 'Login successful.',
        );
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse(
            data: [
                'user' => new UserResource($user),
                'requires_email_verification' =>
                    ! $user->hasVerifiedEmail(),
            ],
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request
            ->user()
            ->currentAccessToken()
            ?->delete();

        return $this->successResponse(
            message: 'Logged out successfully.',
        );
    }

    public function logoutAll(
        Request $request,
    ): JsonResponse {
        $request
            ->user()
            ->tokens()
            ->delete();

        return $this->successResponse(
            message: 'Logged out from all devices successfully.',
        );
    }

    /*
     * Keep account-status messages consistent
     * across all authentication methods.
     */
    private function accountStatusMessage(
        ?string $status,
    ): string {
        return match ($status) {
            'blocked' =>
                'Your account has been blocked.',

            'inactive' =>
                'Your account is inactive.',

            'pending' =>
                'Your account is pending approval.',

            default =>
                'Your account is unavailable.',
        };
    }
}