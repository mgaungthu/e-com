<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Payment Confirmed
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
<table
    role="presentation"
    width="100%"
    cellspacing="0"
    cellpadding="0"
    border="0"
    style="
        width: 100%;
        background-color: #FFFCF7;
        padding: 32px 16px;
    "
>
    <tr>
        <td align="center">
            <table
                role="presentation"
                width="100%"
                cellspacing="0"
                cellpadding="0"
                border="0"
                style="
                    width: 100%;
                    max-width: 520px;
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
                            background-color: #8A1B28;
                            padding: 28px 24px 26px;
                        "
                    >
                        <img
                            src="https://www.burmeseshaveclub.com/assets/logo-white.png"
                            alt="Burmese Shave Club"
                            width="170"
                            style="
                                display: block;
                                width: 170px;
                                max-width: 170px;
                                height: auto;
                                margin: 0 auto;
                                border: 0;
                            "
                        >

                        <div
                            style="
                                margin-top: 14px;
                                color: #FFFCF2;
                                font-size: 11px;
                                line-height: 18px;
                                font-weight: 700;
                                letter-spacing: 1.5px;
                                text-transform: uppercase;
                            "
                        >
                            Payment Confirmed
                        </div>
                    </td>
                </tr>

                {{-- Content --}}
                <tr>
                    <td
                        style="
                            padding: 36px 32px 32px;
                        "
                    >
                        {{-- Success badge --}}
                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                        >
                            <tr>
                                <td align="center">
                                    <div
                                        style="
                                            display: inline-block;
                                            width: 56px;
                                            height: 56px;
                                            line-height: 56px;
                                            text-align: center;
                                            background-color: #EAF6F1;
                                            border-radius: 50%;
                                            color: #16845B;
                                            font-size: 28px;
                                            font-weight: 700;
                                        "
                                    >
                                        ✓
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <h1
                            style="
                                margin: 22px 0 0;
                                text-align: center;
                                color: #21191A;
                                font-size: 25px;
                                line-height: 1.3;
                                font-weight: 700;
                            "
                        >
                            Payment Confirmed
                        </h1>

                        <p
                            style="
                                margin: 18px 0 0;
                                text-align: center;
                                color: #8B8B95;
                                font-size: 15px;
                                line-height: 1.7;
                            "
                        >
                            Thank you,
                            {{ $order->customer?->name ?? 'Customer' }}.
                        </p>

                        <p
                            style="
                                margin: 10px 0 0;
                                text-align: center;
                                color: #8B8B95;
                                font-size: 15px;
                                line-height: 1.7;
                            "
                        >
                            We have confirmed your payment for order
                            <strong style="color: #21191A;">
                                {{ $order->order_number }}
                            </strong>.
                        </p>

                        {{-- Payment Summary --}}
                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                            style="
                                width: 100%;
                                margin-top: 30px;
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
                                            color: #8B8B95;
                                            font-size: 11px;
                                            line-height: 16px;
                                            font-weight: 700;
                                            letter-spacing: 0.7px;
                                            text-transform: uppercase;
                                        "
                                    >
                                        Order Number
                                    </div>

                                    <div
                                        style="
                                            margin-top: 5px;
                                            color: #21191A;
                                            font-size: 15px;
                                            line-height: 22px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ $order->order_number }}
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td
                                    style="
                                        padding: 0 18px;
                                    "
                                >
                                    <div
                                        style="
                                            height: 1px;
                                            background-color: #E8E5E1;
                                        "
                                    ></div>
                                </td>
                            </tr>

                            <tr>
                                <td
                                    style="
                                        padding: 18px;
                                    "
                                >
                                    <div
                                        style="
                                            color: #8B8B95;
                                            font-size: 11px;
                                            line-height: 16px;
                                            font-weight: 700;
                                            letter-spacing: 0.7px;
                                            text-transform: uppercase;
                                        "
                                    >
                                        Amount Paid
                                    </div>

                                    <div
                                        style="
                                            margin-top: 5px;
                                            color: #16845B;
                                            font-size: 22px;
                                            line-height: 28px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ number_format((float) $order->grand_total) }}
                                        MMK
                                    </div>
                                </td>
                            </tr>
                        </table>

                        {{-- Status --}}
                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                            style="
                                width: 100%;
                                margin-top: 24px;
                                background-color: #F1FAF6;
                                border-left: 3px solid #16845B;
                                border-radius: 10px;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding: 15px 16px;
                                        color: #21191A;
                                        font-size: 13px;
                                        line-height: 21px;
                                    "
                                >
                                    Your payment has been successfully confirmed.
                                    Your order is now being prepared.
                                </td>
                            </tr>
                        </table>

                        <p
                            style="
                                margin: 22px 0 0;
                                color: #8B8B95;
                                font-size: 12px;
                                line-height: 1.7;
                                text-align: center;
                            "
                        >
                            We will keep you updated as your order moves
                            through the next steps.
                        </p>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td
                        align="center"
                        style="
                            background-color: #FFFCF7;
                            border-top: 1px solid #E8E5E1;
                            padding: 22px 24px;
                        "
                    >
                        <div
                            style="
                                color: #21191A;
                                font-size: 12px;
                                line-height: 18px;
                                font-weight: 600;
                            "
                        >
                            Burmese Shave Club
                        </div>

                        <div
                            style="
                                margin-top: 5px;
                                color: #8B8B95;
                                font-size: 11px;
                                line-height: 17px;
                            "
                        >
                            Thank you for shopping with us.
                        </div>

                        <div
                            style="
                                margin-top: 8px;
                                color: #8B8B95;
                                font-size: 10px;
                                line-height: 16px;
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