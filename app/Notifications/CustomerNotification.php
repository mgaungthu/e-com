<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $notificationType,
        private readonly string $title,
        private readonly string $body,
        private readonly array $data = [],
    ) {
    }

    public function via(object $notifiable): array
    {
        return [
            'database',
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->notificationType,

            'title' => $this->title,

            'body' => $this->body,

            'data' => $this->data,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}