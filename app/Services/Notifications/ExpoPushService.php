<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExpoPushService
{
    private const MAX_BATCH_SIZE = 100;

    /**
     * Send notification to one user.
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        array $data = [],
    ): void {
        $tokens = UserDevice::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereNotNull('push_token')
            ->where('push_token', '!=', '')
            ->pluck('push_token');

        $this->sendToTokens(
            $tokens,
            $title,
            $body,
            $data,
        );
    }

    /**
     * Send notification to multiple users.
     *
     * @param iterable<User> $users
     */
    public function sendToUsers(
        iterable $users,
        string $title,
        string $body,
        array $data = [],
    ): void {
        $userIds = collect($users)
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $tokens = UserDevice::query()
            ->whereIn('user_id', $userIds)
            ->where('is_active', true)
            ->whereNotNull('push_token')
            ->where('push_token', '!=', '')
            ->pluck('push_token');

        $this->sendToTokens(
            $tokens,
            $title,
            $body,
            $data,
        );
    }

    /**
     * Send notification directly to Expo push tokens.
     */
    public function sendToTokens(
        Collection $tokens,
        string $title,
        string $body,
        array $data = [],
    ): void {
        $tokens = $tokens
            ->filter(
                fn ($token) =>
                    is_string($token)
                    && trim($token) !== ''
            )
            ->map(
                fn (string $token) =>
                    trim($token)
            )
            ->unique()
            ->values();

        if ($tokens->isEmpty()) {
            return;
        }

        try {
            $tokens
                ->chunk(self::MAX_BATCH_SIZE)
                ->each(
                    function (Collection $chunk) use (
                        $title,
                        $body,
                        $data,
                    ) {
                        $this->sendBatch(
                            $chunk,
                            $title,
                            $body,
                            $data,
                        );
                    }
                );
        } catch (Throwable $exception) {
            /*
             * Push notification failure must NOT
             * break the chat message API.
             */

            Log::warning(
                'Expo push notification failed.',
                [
                    'message' =>
                        $exception->getMessage(),

                    'exception' =>
                        $exception::class,
                ],
            );
        }
    }

    /**
     * Send one batch to Expo.
     */
    private function sendBatch(
        Collection $tokens,
        string $title,
        string $body,
        array $data,
    ): void {
        $messages = $tokens
            ->map(
                fn (string $token) => [
                    'to' => $token,

                    'title' => $title,

                    'body' => $body,

                    'data' => $data,

                    /*
                     * Must match the Android channel
                     * created by the React Native app.
                     */
                    'channelId' => 'messages',

                    'priority' => 'high',
                ]
            )
            ->values()
            ->all();

        $request = Http::acceptJson()
            ->asJson()
            ->timeout(5);

        $accessToken =
            config('services.expo.access_token');

        if (
            is_string($accessToken)
            && trim($accessToken) !== ''
        ) {
            $request =
                $request->withToken(
                    trim($accessToken)
                );
        }

        $response = $request->post(
            config(
                'services.expo.push_url',
                'https://exp.host/--/api/v2/push/send'
            ),
            $messages,
        );

        if (!$response->successful()) {
            Log::warning(
                'Expo push API returned an unsuccessful response.',
                [
                    'status' =>
                        $response->status(),

                    'body' =>
                        $response->body(),
                ],
            );

            return;
        }

        $tickets =
            collect(
                $response->json(
                    'data',
                    []
                )
            );

        $this->handleTickets(
            $tokens,
            $tickets,
        );
    }

    /**
     * Handle immediate Expo push ticket errors.
     */
    private function handleTickets(
        Collection $tokens,
        Collection $tickets,
    ): void {
        foreach ($tickets as $index => $ticket) {
            if (!is_array($ticket)) {
                continue;
            }

            if (
                ($ticket['status'] ?? null)
                !== 'error'
            ) {
                continue;
            }

            $token =
                $tokens->get($index);

            $error =
                data_get(
                    $ticket,
                    'details.error'
                );

            /*
             * Expo says this token/device is no
             * longer registered.
             */
            if (
                $token
                && $error ===
                    'DeviceNotRegistered'
            ) {
                UserDevice::query()
                    ->where(
                        'push_token',
                        $token,
                    )
                    ->update([
                        'push_token' => null,
                        'is_active' => false,
                    ]);
            }

            Log::warning(
                'Expo push ticket returned an error.',
                [
                    'token' =>
                        $token,

                    'error' =>
                        $error,

                    'message' =>
                        $ticket['message']
                            ?? null,
                ],
            );
        }
    }
}