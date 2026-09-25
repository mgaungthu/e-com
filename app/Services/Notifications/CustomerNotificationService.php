<?php

namespace App\Services\Notifications;

use App\Models\Product;
use App\Models\User;
use App\Notifications\CustomerNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class CustomerNotificationService
{
    public function __construct(
        private readonly ExpoPushService $expoPushService,
    ) {
    }

    public function send(
        string $type,
        string $title,
        string $body,
        ?Product $product = null,
    ): int {
        $sentCount = 0;

        User::query()
            ->role('customer')
            ->where('status', 'active')
            ->orderBy('id')
            ->chunkById(
                100,
                function (Collection $users) use (
                    $type,
                    $title,
                    $body,
                    $product,
                    &$sentCount,
                ): void {
                    $data = $this->buildData(
                        type: $type,
                        product: $product,
                    );

                    Notification::send(
                        $users,
                        new CustomerNotification(
                            notificationType: $type,
                            title: $title,
                            body: $body,
                            data: $data,
                        ),
                    );

                    $this->expoPushService->sendToUsers(
                        $users,
                        $title,
                        $body,
                        [
                            'type' => $type,
                            ...$data,
                        ],
                    );

                    $sentCount += $users->count();
                },
            );

        return $sentCount;
    }

    private function buildData(
        string $type,
        ?Product $product,
    ): array {
        if (
            !in_array(
                $type,
                [
                    'new_product',
                    'product_restocked',
                ],
                true,
            )
            || $product === null
        ) {
            return [];
        }

        return [
            'product_id' => $product->id,
            'product_slug' => $product->slug,
        ];
    }
}