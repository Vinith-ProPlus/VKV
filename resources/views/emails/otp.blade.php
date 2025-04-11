<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
</head>
<body style="background-color:#edf2f7; margin:0; padding:40px 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table width="570" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:10px; padding:30px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1);">
                <tr>
                    <td align="center" style="padding-bottom:20px;">
                        <h2 style="color:#2d3748;">Your OTP for Password Reset</h2>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="font-size:16px; color:#4a5568;">Hello {{ $user->name }},</p>
                        <p style="font-size:14px; color:#4a5568;">Use the OTP below to reset your password. It is valid for 10 minutes.</p>
                        <p align="center" style="font-size:24px; font-weight:bold; color:#2d3748; margin:20px 0;">{{ $otpData['otp'] }}</p>
                        <p style="font-size:14px; color:#718096;">If you did not request this, please ignore this email.</p>
                        <p style="font-size:14px; color:#4a5568;">Regards,<br><strong>{{ env('APP_NAME') }} Team</strong></p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="border-top:1px solid #e8e5ef; padding-top:15px; color:#b0adc5; font-size:12px;">
                        © {{ now()->year }} {{ env('APP_NAME') }}. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
