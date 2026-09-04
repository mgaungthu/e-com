<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>
        Payment Confirmed
    </title>
</head>

<body>
    <h1>
        Payment Confirmed
    </h1>

    <p>
        Thank you,
        {{ $order->customer?->name ?? 'Customer' }}.
    </p>

    <p>
        We have confirmed your payment for order
        <strong>
            {{ $order->order_number }}
        </strong>.
    </p>

    <p>
        Amount:
        <strong>
            {{ number_format((float) $order->grand_total) }}
            MMK
        </strong>
    </p>

    <p>
        Your order is now being prepared.
    </p>
</body>
</html>