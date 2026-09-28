<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Reset your password
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
                    max-width: 520px;
                    background-color: #FFFFFF;
                    border: 1px solid #E8E5E1;
                    border-radius: 20px;
                    overflow: hidden;
                "
            >
                {{-- Header --}}
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
                            padding: 36px 32px 32px;
                        "
                    >
                        <h1
                            style="
                                margin: 0;
                                color: #21191A;
                                font-size: 25px;
                                line-height: 1.3;
                                font-weight: 700;
                            "
                        >
                            Reset your password
                        </h1>

                        <p
                            style="
                                margin: 18px 0 0;
                                color: #8B8B95;
                                font-size: 15px;
                                line-height: 1.7;
                            "
                        >
                            Hi {{ $user->first_name ?? 'there' }},
                        </p>

                        <p
                            style="
                                margin: 10px 0 0;
                                color: #8B8B95;
                                font-size: 15px;
                                line-height: 1.7;
                            "
                        >
                            Enter the code below in the
                            Burmese Shave Club app to reset
                            your password.
                        </p>

                        {{-- Verification Code --}}
                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                            style="
                                width: 100%;
                                margin: 30px 0;
                                background-color: #F5F5F4;
                                border: 1px solid #E8E5E1;
                                border-radius: 16px;
                            "
                        >
                            <tr>
                                <td
                                    align="center"
                                    style="
                                        padding: 24px 20px;
                                    "
                                >
                                    <div
                                        style="
                                            color: #8A1B28;
                                            font-size: 11px;
                                            line-height: 1.5;
                                            font-weight: 700;
                                            letter-spacing: 1.5px;
                                            text-transform: uppercase;
                                        "
                                    >
                                        Password Reset Code
                                    </div>

                                    <div
                                        style="
                                            margin-top: 12px;
                                            color: #21191A;
                                            font-size: 36px;
                                            line-height: 1.2;
                                            font-weight: 800;
                                            letter-spacing: 8px;
                                        "
                                    >
                                        {{ $code }}
                                    </div>
                                </td>
                            </tr>
                        </table>

                        {{-- Expiry notice --}}
                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                            style="
                                width: 100%;
                                margin: 0;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        background-color: #FFFCF7;
                                        border-left: 3px solid #D89B16;
                                        padding: 12px 14px;
                                        color: #8B8B95;
                                        font-size: 13px;
                                        line-height: 1.7;
                                    "
                                >
                                    This code expires in
                                    <strong style="color: #21191A;">
                                        {{ $expiresInMinutes }} minutes
                                    </strong>.
                                </td>
                            </tr>
                        </table>

                        <p
                            style="
                                margin: 22px 0 0;
                                color: #8B8B95;
                                font-size: 12px;
                                line-height: 1.7;
                            "
                        >
                            If you did not request a password
                            reset, you can safely ignore this
                            email. Your password will remain
                            unchanged.
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
                            color: #8B8B95;
                            font-size: 11px;
                            line-height: 1.6;
                        "
                    >
                        © {{ date('Y') }} Burmese Shave Club.
                        All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>