<?php

namespace App\Listeners;

use App\Events\OrderPaymentConfirmed;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendPaymentConfirmationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $timeout = 60;

    public function handle(OrderPaymentConfirmed $event): void
    {
        $order = $event->order;

        /*
        |--------------------------------------------------------------------------
        | Load Email Data
        |--------------------------------------------------------------------------
        */

        $order->loadMissing([
            'customer',
            'items',
            'latestPayment.paymentMethod',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Customer Email
        |--------------------------------------------------------------------------
        */

        $email = $order->customer?->email;

        if (! $email) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Send Payment Confirmation
        |--------------------------------------------------------------------------
        */

        Mail::to($email)->send(
            new PaymentConfirmationMail($order),
        );
    }
}