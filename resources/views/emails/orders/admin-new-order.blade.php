<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        New Order Received - {{ $order->order_number }}
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background-color: #FFFCF7;
        font-family: Arial, Helvetica, sans-serif;
        color: #21191A;
    "
>
    @php
        $customer = $order->customer;

        $shipping = $order->shipping_address ?? [];

        $payment = $order->latestPayment;

        $recipientName =
            $shipping['recipient_name']
            ?? $customer?->name
            ?? 'Customer';

        $paymentMethodName =
            $payment?->method_name
            ?? $payment?->paymentMethod?->name
            ?? $order->payment_method
            ?? 'Payment';

        $paymentMethodCode = strtolower(
            (string) (
                $payment?->method_code
                ?? $order->payment_method
                ?? ''
            )
        );

        $isCashOnDelivery =
            $payment?->paymentMethod?->type === 'cod'
            || $paymentMethodCode === 'cod'
            || str_contains(
                strtolower($paymentMethodName),
                'cash on delivery'
            );

        $paymentStatus = $isCashOnDelivery
            ? 'Cash on Delivery'
            : ucfirst(
                str_replace(
                    '_',
                    ' ',
                    (string) ($payment?->status ?? 'pending')
                )
            );

        $addressParts = array_filter([
            $shipping['address_line_one'] ?? null,
            $shipping['address_line_two'] ?? null,

            $shipping['building'] ?? null
                ? 'Building '.$shipping['building']
                : null,

            $shipping['floor'] ?? null
                ? 'Floor '.$shipping['floor']
                : null,

            $shipping['unit'] ?? null
                ? 'Unit '.$shipping['unit']
                : null,

            $shipping['township'] ?? null,
            $shipping['city'] ?? null,
            $shipping['state'] ?? null,
            $shipping['postal_code'] ?? null,
        ]);
    @endphp

    <table
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        role="presentation"
        style="
            width: 100%;
            background-color: #FFFCF7;
        "
    >
        <tr>
            <td
                align="center"
                style="
                    padding: 32px 16px;
                "
            >
                <table
                    width="100%"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    role="presentation"
                    style="
                        width: 100%;
                        max-width: 620px;
                        background-color: #FFFFFF;
                        border: 1px solid #E8E5E1;
                        border-radius: 20px;
                        overflow: hidden;
                    "
                >
                    {{-- Header --}}
                    <tr>
                        <td
                            align="center"
                            style="
                                padding: 28px 24px 26px;
                                background-color: #8A1B28;
                            "
                        >
                            <img
                                src="https://www.burmeseshaveclub.com/assets/logo-white.png"
                                alt="Burmese Shave Club"
                                width="170"
                                style="
                                    display: block;
                                    width: 150px;
                                    max-width: 150px;
                                    height: auto;
                                    margin: 0 auto;
                                    border: 0;
                                "
                            >

                            
                        </td>
                    </tr>

                    {{-- Content --}}
                    <tr>
                        <td
                            style="
                                padding: 34px 32px 32px;
                            "
                        >
                            <h1
                                style="
                                    margin: 0;
                                    font-size: 24px;
                                    line-height: 32px;
                                    font-weight: 700;
                                    color: #21191A;
                                "
                            >
                                New order received
                            </h1>

                            <p
                                style="
                                    margin: 12px 0 0;
                                    font-size: 14px;
                                    line-height: 22px;
                                    color: #8B8B95;
                                "
                            >
                                A customer has placed a new order on Burmese Shave Club.
                            </p>

                            {{-- Order Info --}}
                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                role="presentation"
                                style="
                                    margin-top: 28px;
                                    background-color: #F5F5F4;
                                    border: 1px solid #E8E5E1;
                                    border-radius: 14px;
                                "
                            >
                                <tr>
                                    <td
                                        style="
                                            padding: 18px;
                                        "
                                    >
                                        <div
                                            style="
                                                font-size: 11px;
                                                font-weight: 700;
                                                color: #8A1B28;
                                                text-transform: uppercase;
                                                letter-spacing: 0.6px;
                                            "
                                        >
                                            Order Number
                                        </div>

                                        <div
                                            style="
                                                margin-top: 5px;
                                                font-size: 17px;
                                                font-weight: 700;
                                                color: #21191A;
                                            "
                                        >
                                            {{ $order->order_number }}
                                        </div>
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 18px;
                                        "
                                    >
                                        <div
                                            style="
                                                font-size: 11px;
                                                font-weight: 700;
                                                color: #8A1B28;
                                                text-transform: uppercase;
                                                letter-spacing: 0.6px;
                                            "
                                        >
                                            Order Date
                                        </div>

                                        <div
                                            style="
                                                margin-top: 5px;
                                                font-size: 13px;
                                                font-weight: 600;
                                                color: #21191A;
                                            "
                                        >
                                            {{ $order->created_at?->format('d M Y, h:i A') }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            {{-- Customer --}}
                            <h2
                                style="
                                    margin: 32px 0 0;
                                    font-size: 15px;
                                    line-height: 22px;
                                    font-weight: 700;
                                    color: #21191A;
                                "
                            >
                                Customer
                            </h2>

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                role="presentation"
                                style="
                                    margin-top: 12px;
                                    background-color: #F5F5F4;
                                    border: 1px solid #E8E5E1;
                                    border-radius: 12px;
                                "
                            >
                                <tr>
                                    <td
                                        style="
                                            padding: 18px;
                                            font-size: 13px;
                                            line-height: 21px;
                                            color: #8B8B95;
                                        "
                                    >
                                        <strong style="color: #21191A;">
                                            {{ $customer?->name ?? $recipientName }}
                                        </strong>

                                        @if ($customer?->email)
                                            <br>
                                            {{ $customer->email }}
                                        @endif

                                        @if (! empty($shipping['phone']))
                                            <br>
                                            {{ $shipping['phone'] }}
                                        @endif

                                        @if (! empty($shipping['alternate_phone']))
                                            <br>
                                            {{ $shipping['alternate_phone'] }}
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            {{-- Items --}}
                            <h2
                                style="
                                    margin: 32px 0 0;
                                    font-size: 15px;
                                    line-height: 22px;
                                    font-weight: 700;
                                    color: #21191A;
                                "
                            >
                                Order Items
                            </h2>

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                role="presentation"
                                style="
                                    margin-top: 12px;
                                    border-collapse: collapse;
                                "
                            >
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td
                                            style="
                                                padding: 15px 0;
                                                border-bottom: 1px solid #E8E5E1;
                                            "
                                        >
                                            <div
                                                style="
                                                    font-size: 14px;
                                                    line-height: 20px;
                                                    font-weight: 600;
                                                    color: #21191A;
                                                "
                                            >
                                                {{ $item->product_name }}
                                            </div>

                                            @if ($item->sku)
                                                <div
                                                    style="
                                                        margin-top: 3px;
                                                        font-size: 11px;
                                                        color: #8B8B95;
                                                    "
                                                >
                                                    SKU: {{ $item->sku }}
                                                </div>
                                            @endif

                                            <div
                                                style="
                                                    margin-top: 5px;
                                                    font-size: 12px;
                                                    color: #8B8B95;
                                                "
                                            >
                                                {{ number_format((float) $item->unit_price) }}
                                                MMK
                                                ×
                                                {{ $item->quantity }}
                                            </div>
                                        </td>

                                        <td
                                            align="right"
                                            style="
                                                padding: 15px 0 15px 16px;
                                                border-bottom: 1px solid #E8E5E1;
                                                font-size: 13px;
                                                font-weight: 700;
                                                color: #21191A;
                                                white-space: nowrap;
                                            "
                                        >
                                            {{ number_format((float) $item->line_total) }}
                                            MMK
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            {{-- Price Summary --}}
                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                role="presentation"
                                style="
                                    margin-top: 24px;
                                "
                            >
                                <tr>
                                    <td
                                        style="
                                            padding: 5px 0;
                                            font-size: 13px;
                                            color: #8B8B95;
                                        "
                                    >
                                        Subtotal
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 5px 0;
                                            font-size: 13px;
                                            color: #21191A;
                                        "
                                    >
                                        {{ number_format((float) $order->subtotal) }}
                                        MMK
                                    </td>
                                </tr>

                                @if ((float) $order->discount_total > 0)
                                    <tr>
                                        <td
                                            style="
                                                padding: 5px 0;
                                                font-size: 13px;
                                                color: #8B8B95;
                                            "
                                        >
                                            Discount
                                        </td>

                                        <td
                                            align="right"
                                            style="
                                                padding: 5px 0;
                                                font-size: 13px;
                                                color: #16845B;
                                            "
                                        >
                                            -
                                            {{ number_format((float) $order->discount_total) }}
                                            MMK
                                        </td>
                                    </tr>
                                @endif

                                <tr>
                                    <td
                                        style="
                                            padding: 5px 0;
                                            font-size: 13px;
                                            color: #8B8B95;
                                        "
                                    >
                                        Shipping
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 5px 0;
                                            font-size: 13px;
                                            color: #21191A;
                                        "
                                    >
                                        {{ number_format((float) $order->shipping_total) }}
                                        MMK
                                    </td>
                                </tr>

                                @if ((float) $order->tax_total > 0)
                                    <tr>
                                        <td
                                            style="
                                                padding: 5px 0;
                                                font-size: 13px;
                                                color: #8B8B95;
                                            "
                                        >
                                            Tax
                                        </td>

                                        <td
                                            align="right"
                                            style="
                                                padding: 5px 0;
                                                font-size: 13px;
                                                color: #21191A;
                                            "
                                        >
                                            {{ number_format((float) $order->tax_total) }}
                                            MMK
                                        </td>
                                    </tr>
                                @endif

                                <tr>
                                    <td
                                        style="
                                            padding-top: 16px;
                                            border-top: 1px solid #E8E5E1;
                                            font-size: 14px;
                                            font-weight: 700;
                                            color: #21191A;
                                        "
                                    >
                                        Grand Total
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding-top: 16px;
                                            border-top: 1px solid #E8E5E1;
                                            font-size: 20px;
                                            font-weight: 700;
                                            color: #8A1B28;
                                        "
                                    >
                                        {{ number_format((float) $order->grand_total) }}
                                        MMK
                                    </td>
                                </tr>
                            </table>

                            {{-- Payment --}}
                            <h2
                                style="
                                    margin: 32px 0 0;
                                    font-size: 15px;
                                    line-height: 22px;
                                    font-weight: 700;
                                    color: #21191A;
                                "
                            >
                                Payment
                            </h2>

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                role="presentation"
                                style="
                                    margin-top: 12px;
                                    background-color: #FFFCF7;
                                    border: 1px solid #E8E5E1;
                                    border-left: 4px solid #D89B16;
                                    border-radius: 12px;
                                "
                            >
                                <tr>
                                    <td
                                        style="
                                            padding: 18px;
                                        "
                                    >
                                        <div
                                            style="
                                                font-size: 11px;
                                                font-weight: 700;
                                                color: #8B8B95;
                                                text-transform: uppercase;
                                            "
                                        >
                                            Payment Method
                                        </div>

                                        <div
                                            style="
                                                margin-top: 5px;
                                                font-size: 14px;
                                                font-weight: 700;
                                                color: #21191A;
                                            "
                                        >
                                            {{ $paymentMethodName }}
                                        </div>

                                        @if ($payment?->reference_number)
                                            <div
                                                style="
                                                    margin-top: 8px;
                                                    font-size: 12px;
                                                    color: #8B8B95;
                                                "
                                            >
                                                Reference:
                                                {{ $payment->reference_number }}
                                            </div>
                                        @endif
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 18px;
                                        "
                                    >
                                        <div
                                            style="
                                                font-size: 11px;
                                                font-weight: 700;
                                                color: #8B8B95;
                                                text-transform: uppercase;
                                            "
                                        >
                                            Status
                                        </div>

                                        <div
                                            style="
                                                margin-top: 5px;
                                                font-size: 13px;
                                                font-weight: 700;
                                                color: #D89B16;
                                            "
                                        >
                                            {{ $paymentStatus }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            {{-- Delivery Address --}}
                            <h2
                                style="
                                    margin: 32px 0 0;
                                    font-size: 15px;
                                    line-height: 22px;
                                    font-weight: 700;
                                    color: #21191A;
                                "
                            >
                                Delivery Address
                            </h2>

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                role="presentation"
                                style="
                                    margin-top: 12px;
                                    background-color: #F5F5F4;
                                    border: 1px solid #E8E5E1;
                                    border-radius: 12px;
                                "
                            >
                                <tr>
                                    <td
                                        style="
                                            padding: 18px;
                                            font-size: 13px;
                                            line-height: 21px;
                                            color: #8B8B95;
                                        "
                                    >
                                        <strong style="color: #21191A;">
                                            {{ $recipientName }}
                                        </strong>

                                        @if (! empty($shipping['phone']))
                                            <br>
                                            {{ $shipping['phone'] }}
                                        @endif

                                        @if (! empty($shipping['alternate_phone']))
                                            <br>
                                            {{ $shipping['alternate_phone'] }}
                                        @endif

                                        @if (count($addressParts) > 0)
                                            <br><br>
                                            {{ implode(', ', $addressParts) }}
                                        @endif

                                        @if (! empty($shipping['landmark']))
                                            <br>
                                            Landmark:
                                            {{ $shipping['landmark'] }}
                                        @endif

                                        @if (! empty($shipping['delivery_instruction']))
                                            <br><br>

                                            <strong style="color: #21191A;">
                                                Delivery instruction:
                                            </strong>

                                            {{ $shipping['delivery_instruction'] }}
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            {{-- Notes --}}
                            @if ($order->notes)
                                <h2
                                    style="
                                        margin: 32px 0 0;
                                        font-size: 15px;
                                        color: #21191A;
                                    "
                                >
                                    Order Notes
                                </h2>

                                <div
                                    style="
                                        margin-top: 12px;
                                        padding: 16px;
                                        background-color: #F5F5F4;
                                        border: 1px solid #E8E5E1;
                                        border-radius: 12px;
                                        font-size: 13px;
                                        line-height: 20px;
                                        color: #8B8B95;
                                    "
                                >
                                    {{ $order->notes }}
                                </div>
                            @endif
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            align="center"
                            style="
                                padding: 24px 32px;
                                background-color: #FFFCF7;
                                border-top: 1px solid #E8E5E1;
                            "
                        >
                            <div
                                style="
                                    font-size: 12px;
                                    font-weight: 600;
                                    color: #21191A;
                                "
                            >
                                Burmese Shave Club
                            </div>

                            <div
                                style="
                                    margin-top: 5px;
                                    font-size: 11px;
                                    color: #8B8B95;
                                "
                            >
                                Admin Order Notification
                            </div>

                            <div
                                style="
                                    margin-top: 8px;
                                    font-size: 10px;
                                    color: #8B8B95;
                                "
                            >
                                © {{ date('Y') }} Burmese Shave Club.
                                All rights reserved.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>