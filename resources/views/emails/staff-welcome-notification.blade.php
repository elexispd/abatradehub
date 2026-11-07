<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to AbaTradeHub</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f7f9fb; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1c1d1f;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f7f9fb; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #9f3e07; padding: 24px 32px; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.02em;">AbaTradeHub</h1>
                            <p style="margin: 4px 0 0 0; font-size: 13px; opacity: 0.9;">Staff & Operations Portal</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px;">
                            <h2 style="margin: 0 0 16px 0; font-size: 18px; color: #0f172a;">Welcome to the Team, {{ $user->name }}!</h2>
                            <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #334155;">
                                A staff account has been created for you on the AbaTradeHub admin portal. Through this panel, you will help coordinate orders, suppliers, and wholesale buyer enquiries.
                            </p>

                            <!-- Account Details Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin: 20px 0; padding: 16px;">
                                <tr>
                                    <td style="font-size: 13px; color: #64748b; padding: 4px 0;">Email / Login:</td>
                                    <td style="font-size: 13px; font-weight: 600; color: #0f172a; padding: 4px 0;">{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 13px; color: #64748b; padding: 4px 0;">Assigned Roles:</td>
                                    <td style="font-size: 13px; font-weight: 600; color: #0f172a; padding: 4px 0;">
                                        {{ $user->roles->pluck('name')->join(', ') ?: 'Staff Member' }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #334155;">
                                For security reasons, please activate your account and choose your personal password by clicking the button below:
                            </p>

                            <!-- Primary CTA Button -->
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
                                <tr>
                                    <td align="center" style="border-radius: 8px; background-color: #9f3e07;">
                                        <a href="{{ $resetUrl }}" target="_blank" style="display: inline-block; padding: 12px 28px; font-size: 14px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px;">
                                            Set Your Password &amp; Login
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 24px 0 0 0; font-size: 12px; color: #64748b; line-height: 1.5;">
                                Alternatively, you can copy and paste this link into your browser:<br>
                                <a href="{{ $resetUrl }}" style="color: #9f3e07; word-break: break-all;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f1f5f9; padding: 16px 32px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #64748b;">
                            AbaTradeHub B2B Wholesale Portal &bull; Ariaria International Market, Aba &bull; Internal staff communication.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

