<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\OrderConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail implements ShouldQueue
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

        $email = $order->customer?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->send(
            new OrderConfirmationMail($order),
        );
    }
}