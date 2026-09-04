<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Order Received - {{ $order->order_number }}
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background-color: #f4f6f8;
        font-family: Arial, Helvetica, sans-serif;
        color: #111827;
    "
>
    @php
        $shipping = $order->shipping_address ?? [];
        $payment = $order->latestPayment;

        $recipientName =
            $shipping['recipient_name']
            ?? $order->customer?->name
            ?? 'Customer';

        $paymentMethodName =
            $payment?->method_name
            ?? $order->payment_method
            ?? 'Payment';

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
        role="presentation"
        style="
            width: 100%;
            background-color: #f4f6f8;
        "
    >
        <tr>
            <td
                align="center"
                style="
                    padding: 40px 16px;
                "
            >
                <table
                    width="100%"
                    cellpadding="0"
                    cellspacing="0"
                    role="presentation"
                    style="
                        width: 100%;
                        max-width: 620px;
                        background-color: #ffffff;
                        border-radius: 18px;
                        overflow: hidden;
                    "
                >
                    {{-- Header --}}
                    <tr>
                        <td
                            style="
                                padding: 30px 32px;
                                background-color: #0e1629;
                            "
                        >
                            <div
                                style="
                                    font-size: 21px;
                                    line-height: 28px;
                                    font-weight: 700;
                                    color: #ffffff;
                                "
                            >
                                Burmese Shave Club
                            </div>

                            <div
                                style="
                                    margin-top: 6px;
                                    font-size: 12px;
                                    line-height: 18px;
                                    color: #cbd5e1;
                                "
                            >
                                ORDER CONFIRMATION
                            </div>
                        </td>
                    </tr>

                    {{-- Main content --}}
                    <tr>
                        <td
                            style="
                                padding: 32px;
                            "
                        >
                            <h1
                                style="
                                    margin: 0;
                                    font-size: 24px;
                                    line-height: 32px;
                                    color: #111827;
                                "
                            >
                                Thank you, {{ $recipientName }}
                            </h1>

                            <p
                                style="
                                    margin: 14px 0 0;
                                    font-size: 14px;
                                    line-height: 22px;
                                    color: #64748b;
                                "
                            >
                                We have received your order and payment proof.
                            </p>

                            <p
                                style="
                                    margin: 8px 0 0;
                                    font-size: 14px;
                                    line-height: 22px;
                                    color: #64748b;
                                "
                            >
                                Your payment is currently pending verification.
                                We will notify you again once your payment has
                                been confirmed.
                            </p>

                            {{-- Order info --}}
                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                role="presentation"
                                style="
                                    margin-top: 28px;
                                    background-color: #f8fafc;
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
                                                line-height: 16px;
                                                color: #64748b;
                                                text-transform: uppercase;
                                            "
                                        >
                                            Order Number
                                        </div>

                                        <div
                                            style="
                                                margin-top: 5px;
                                                font-size: 17px;
                                                line-height: 24px;
                                                font-weight: 700;
                                                color: #111827;
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
                                                line-height: 16px;
                                                color: #64748b;
                                                text-transform: uppercase;
                                            "
                                        >
                                            Order Date
                                        </div>

                                        <div
                                            style="
                                                margin-top: 5px;
                                                font-size: 13px;
                                                line-height: 20px;
                                                font-weight: 600;
                                                color: #111827;
                                            "
                                        >
                                            {{ $order->created_at?->format('d M Y, h:i A') }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            {{-- Items --}}
                            <h2
                                style="
                                    margin: 32px 0 0;
                                    font-size: 15px;
                                    line-height: 22px;
                                    color: #111827;
                                "
                            >
                                Order Items
                            </h2>

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
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
                                                border-bottom: 1px solid #e5e7eb;
                                            "
                                        >
                                            <div
                                                style="
                                                    font-size: 14px;
                                                    line-height: 20px;
                                                    font-weight: 600;
                                                    color: #111827;
                                                "
                                            >
                                                {{ $item->product_name }}
                                            </div>

                                            @if ($item->sku)
                                                <div
                                                    style="
                                                        margin-top: 3px;
                                                        font-size: 11px;
                                                        line-height: 16px;
                                                        color: #94a3b8;
                                                    "
                                                >
                                                    SKU: {{ $item->sku }}
                                                </div>
                                            @endif

                                            <div
                                                style="
                                                    margin-top: 5px;
                                                    font-size: 12px;
                                                    line-height: 18px;
                                                    color: #64748b;
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
                                            valign="middle"
                                            style="
                                                padding: 15px 0 15px 16px;
                                                border-bottom: 1px solid #e5e7eb;
                                                font-size: 13px;
                                                line-height: 20px;
                                                font-weight: 700;
                                                color: #111827;
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
                                            color: #64748b;
                                        "
                                    >
                                        Subtotal
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 5px 0;
                                            font-size: 13px;
                                            color: #111827;
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
                                                color: #64748b;
                                            "
                                        >
                                            Discount
                                        </td>

                                        <td
                                            align="right"
                                            style="
                                                padding: 5px 0;
                                                font-size: 13px;
                                                color: #111827;
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
                                            color: #64748b;
                                        "
                                    >
                                        Shipping
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 5px 0;
                                            font-size: 13px;
                                            color: #111827;
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
                                                color: #64748b;
                                            "
                                        >
                                            Tax
                                        </td>

                                        <td
                                            align="right"
                                            style="
                                                padding: 5px 0;
                                                font-size: 13px;
                                                color: #111827;
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
                                            border-top: 1px solid #e5e7eb;
                                            font-size: 14px;
                                            font-weight: 700;
                                            color: #111827;
                                        "
                                    >
                                        Grand Total
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding-top: 16px;
                                            border-top: 1px solid #e5e7eb;
                                            font-size: 20px;
                                            line-height: 26px;
                                            font-weight: 700;
                                            color: #111827;
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
                                    color: #111827;
                                "
                            >
                                Payment
                            </h2>

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                role="presentation"
                                style="
                                    margin-top: 12px;
                                    background-color: #fff7ed;
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
                                                line-height: 16px;
                                                color: #9a3412;
                                            "
                                        >
                                            PAYMENT METHOD
                                        </div>

                                        <div
                                            style="
                                                margin-top: 5px;
                                                font-size: 14px;
                                                font-weight: 700;
                                                color: #7c2d12;
                                            "
                                        >
                                            {{ $paymentMethodName }}
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
                                                line-height: 16px;
                                                color: #9a3412;
                                            "
                                        >
                                            STATUS
                                        </div>

                                        <div
                                            style="
                                                margin-top: 5px;
                                                font-size: 13px;
                                                font-weight: 700;
                                                color: #9a3412;
                                            "
                                        >
                                            Pending Verification
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
                                    color: #111827;
                                "
                            >
                                Delivery Address
                            </h2>

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                role="presentation"
                                style="
                                    margin-top: 12px;
                                    background-color: #f8fafc;
                                    border-radius: 12px;
                                "
                            >
                                <tr>
                                    <td
                                        style="
                                            padding: 18px;
                                            font-size: 13px;
                                            line-height: 21px;
                                            color: #475569;
                                        "
                                    >
                                        <strong
                                            style="
                                                color: #111827;
                                            "
                                        >
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

                                            <strong>
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
                                        color: #111827;
                                    "
                                >
                                    Order Notes
                                </h2>

                                <div
                                    style="
                                        margin-top: 12px;
                                        padding: 16px;
                                        background-color: #f8fafc;
                                        border-radius: 12px;
                                        font-size: 13px;
                                        line-height: 20px;
                                        color: #64748b;
                                    "
                                >
                                    {{ $order->notes }}
                                </div>
                            @endif

                            {{-- Info --}}
                            <div
                                style="
                                    margin-top: 32px;
                                    padding: 18px;
                                    background-color: #eff6ff;
                                    border-radius: 12px;
                                    font-size: 13px;
                                    line-height: 21px;
                                    color: #1e40af;
                                "
                            >
                                We will send you another email after your
                                payment has been verified.
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            align="center"
                            style="
                                padding: 24px 32px;
                                background-color: #f8fafc;
                            "
                        >
                            <div
                                style="
                                    font-size: 12px;
                                    line-height: 18px;
                                    color: #64748b;
                                "
                            >
                                Burmese Shave Club
                            </div>

                            <div
                                style="
                                    margin-top: 5px;
                                    font-size: 11px;
                                    line-height: 17px;
                                    color: #94a3b8;
                                "
                            >
                                Thank you for shopping with us.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>