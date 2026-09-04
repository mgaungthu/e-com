<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Verify your email
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background-color: #f5f4f0;
        font-family: Arial, Helvetica, sans-serif;
        color: #1f2937;
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
        background-color: #f5f4f0;
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
                    background-color: #ffffff;
                    border-radius: 20px;
                    overflow: hidden;
                "
            >
                <tr>
                    <td
                        align="center"
                        style="
                            background-color: #111827;
                            padding: 32px 24px;
                        "
                    >
                        <div
                            style="
                                color: #ffffff;
                                font-size: 22px;
                                font-weight: 700;
                                letter-spacing: 1px;
                            "
                        >
                            BURMESE SHAVE CLUB
                        </div>

                        <div
                            style="
                                margin-top: 8px;
                                color: #cbd5e1;
                                font-size: 13px;
                            "
                        >
                            Premium Grooming Experience
                        </div>
                    </td>
                </tr>

                <tr>
                    <td
                        style="
                            padding: 36px 32px 32px;
                        "
                    >
                        <h1
                            style="
                                margin: 0;
                                color: #111827;
                                font-size: 25px;
                                line-height: 1.3;
                            "
                        >
                            Verify your email
                        </h1>

                        <p
                            style="
                                margin: 18px 0 0;
                                color: #6b7280;
                                font-size: 15px;
                                line-height: 1.7;
                            "
                        >
                            Hi {{ $user->first_name ?? 'there' }},
                        </p>

                        <p
                            style="
                                margin: 10px 0 0;
                                color: #6b7280;
                                font-size: 15px;
                                line-height: 1.7;
                            "
                        >
                            Enter the verification code below
                            in the Burmese Shave Club app to
                            confirm your email address.
                        </p>

                        <div
                            style="
                                margin: 30px 0;
                                padding: 24px;
                                background-color: #f3f4f6;
                                border-radius: 16px;
                                text-align: center;
                            "
                        >
                            <div
                                style="
                                    color: #6b7280;
                                    font-size: 11px;
                                    font-weight: 700;
                                    letter-spacing: 1.5px;
                                    text-transform: uppercase;
                                "
                            >
                                Verification Code
                            </div>

                            <div
                                style="
                                    margin-top: 12px;
                                    color: #111827;
                                    font-size: 40px;
                                    font-weight: 800;
                                    letter-spacing: 12px;
                                "
                            >
                                {{ $code }}
                            </div>
                        </div>

                        <p
                            style="
                                margin: 0;
                                color: #6b7280;
                                font-size: 13px;
                                line-height: 1.7;
                            "
                        >
                            This code expires in
                            <strong>
                                {{ $expiresInMinutes }} minutes
                            </strong>.
                        </p>

                        <p
                            style="
                                margin: 20px 0 0;
                                color: #9ca3af;
                                font-size: 12px;
                                line-height: 1.7;
                            "
                        >
                            If you did not create a Burmese
                            Shave Club account, you can safely
                            ignore this email.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td
                        align="center"
                        style="
                            border-top: 1px solid #e5e7eb;
                            padding: 22px 24px;
                            color: #9ca3af;
                            font-size: 11px;
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