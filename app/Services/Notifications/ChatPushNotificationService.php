<?php

namespace App\Services\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ChatPushNotificationService
{
    public function __construct(
        private readonly ExpoPushService $expoPushService,
    ) {
    }

    /**
     * Customer sent a message.
     *
     * Notify:
     *
     * - super_admin
     * - admin
     * - customer_support
     */
    public function notifyAdmins(
        Conversation $conversation,
        Message $message,
        User $customer,
    ): void {
        try {
            $recipients = User::query()
                ->role([
                    'super_admin',
                    'admin',
                    'customer_support',
                ])
                ->get();

            if ($recipients->isEmpty()) {
                return;
            }

            $customerName =
                $customer->display_name
                ?: $customer->name
                ?: trim(
                    ($customer->first_name ?? '')
                    . ' '
                    . ($customer->last_name ?? '')
                )
                ?: 'Customer';

            $this->expoPushService
                ->sendToUsers(
                    $recipients,

                    'New support message',

                    $this->messagePreview(
                        $message,
                        $customerName,
                    ),

                    [
                        'type' => 'chat',

                        /*
                         * Admin/support notification routing.
                         */
                        'viewer' => 'admin',

                        'conversationId' =>
                            $conversation->id,

                        'customerName' =>
                            $customerName,
                    ],
                );
        } catch (Throwable $exception) {
            Log::warning(
                'Unable to send admin chat push notification.',
                [
                    'conversation_id' =>
                        $conversation->id,

                    'message_id' =>
                        $message->id,

                    'error' =>
                        $exception->getMessage(),
                ],
            );
        }
    }

    /**
     * Admin/support replied.
     *
     * Notify the customer who owns the conversation.
     */
    public function notifyCustomer(
        Conversation $conversation,
        Message $message,
    ): void {
        try {
            $customer =
                $conversation->user;

            if (!$customer) {
                return;
            }

            $this->expoPushService
                ->sendToUser(
                    $customer,

                    'Burmese Shave Club',

                    $this->adminMessagePreview(
                        $message,
                    ),

                    [
                        'type' => 'chat',

                        /*
                         * Customer only has one support
                         * conversation.
                         */
                        'viewer' => 'customer',
                    ],
                );
        } catch (Throwable $exception) {
            Log::warning(
                'Unable to send customer chat push notification.',
                [
                    'conversation_id' =>
                        $conversation->id,

                    'message_id' =>
                        $message->id,

                    'error' =>
                        $exception->getMessage(),
                ],
            );
        }
    }

    /**
     * Notification body for customer -> admin.
     */
    private function messagePreview(
        Message $message,
        string $customerName,
    ): string {
        $content =
            $this->messageContent(
                $message
            );

        return Str::limit(
            "{$customerName}: {$content}",
            140,
        );
    }

    /**
     * Notification body for admin -> customer.
     */
    private function adminMessagePreview(
        Message $message,
    ): string {
        return Str::limit(
            $this->messageContent(
                $message
            ),
            140,
        );
    }

    /**
     * Build readable notification preview.
     */
    private function messageContent(
        Message $message,
    ): string {
        return match ($message->type) {
            'image' =>
                'Sent a photo',

            'feed' =>
                'Shared a post',

            'product' =>
                'Shared a product',

            'text' =>
                trim(
                    (string) $message->message
                ) !== ''
                    ? trim(
                        (string) $message->message
                    )
                    : 'Sent a message',

            default =>
                'Sent a message',
        };
    }
}