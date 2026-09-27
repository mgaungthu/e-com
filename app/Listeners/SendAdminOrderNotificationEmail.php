<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\AdminNewOrderMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendAdminOrderNotificationEmail implements ShouldQueue
{
    public int $tries = 3;

    public int $timeout = 60;

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        $order->loadMissing([
            'customer',
            'items',
            'latestPayment.paymentMethod',
        ]);

        $email = config('mail.admin_order_email');

        if (! $email) {
            return;
        }

        Mail::to($email)->send(
            new AdminNewOrderMail($order),
        );
    }
}