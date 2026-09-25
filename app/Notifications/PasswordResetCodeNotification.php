<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly int $expiresInMinutes = 10,
    ) {
    }

    public function via(object $notifiable): array
    {
        return [
            'mail',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(
                'Reset your Burmese Shave Club password',
            )
            ->view(
                'emails.auth.reset-password',
                [
                    'user' => $notifiable,
                    'code' => $this->code,
                    'expiresInMinutes' =>
                        $this->expiresInMinutes,
                ],
            );
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}