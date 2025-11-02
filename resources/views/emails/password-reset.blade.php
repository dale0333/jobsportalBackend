<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Password Reset - {{ $appName }}</title>
</head>

<body
    style="font-family: Arial, Helvetica, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 0;">

    <div
        style="max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">

        <!-- Header -->
        <div
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center; color: white;">
            <h1 style="font-size: 26px; font-weight: 700; margin-bottom: 8px;">Password Reset Request</h1>
            <p style="font-size: 16px; opacity: 0.9;">Let's get you back into your account securely!</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">
            <!-- Welcome Section -->
            <div style="text-align: center; margin-bottom: 30px;">
                <!-- Logo Image Only -->
                <div style="margin-bottom: 25px;">
                    <img src="https://yorpnyc.org.ph/images/clark-dark.png" alt="{{ $appName }}"
                        style="height: 80px; width: auto;">
                </div>

                <!-- Content -->
                <h2 style="margin-bottom: 16px; color: #1e293b;">Reset Your Password</h2>
                <p style="color: #64748b; margin-bottom: 10px;">
                    Hello <strong>{{ $user->name }}</strong>, we received a password reset request for your account.
                </p>
                <p style="color: #64748b; margin-bottom: 0;">
                    Click the button below to securely reset your password and regain access to your account.
                </p>
            </div>

            <!-- Reset Button -->
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $resetUrl }}"
                    style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; text-decoration: none; padding: 16px 32px; border-radius: 12px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                    Reset Password
                </a>
            </div>

            <!-- Expiry Notice -->
            <div
                style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 16px; margin: 20px 0; text-align: center; color: #856404;">
                <strong>⏰ Important:</strong> This password reset link will expire in {{ $expiryTime }} minutes.
            </div>

            <!-- Alternative Link -->
            <div style="text-align: center; margin-top: 20px;">
                <p style="color: #64748b; font-size: 14px; margin-bottom: 10px;">Or copy and paste this link in your
                    browser:</p>
                <div
                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; word-break: break-all;">
                    <a href="{{ $resetUrl }}" style="color: #667eea; text-decoration: none;">{{ $resetUrl }}</a>
                </div>
            </div>

            <!-- Security Features -->
            <div style="margin: 30px 0;">
                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td
                            style="text-align: center; padding: 15px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc;">
                            <div style="font-size: 22px; color: #667eea;">🔒</div>
                            <p style="font-weight: 600; color: #1e293b; margin: 5px 0;">Secure Process</p>
                            <p style="font-size: 14px; color: #64748b;">Encrypted & protected</p>
                        </td>
                        <td
                            style="text-align: center; padding: 15px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc;">
                            <div style="font-size: 22px; color: #667eea;">⚡</div>
                            <p style="font-weight: 600; color: #1e293b; margin: 5px 0;">Quick Reset</p>
                            <p style="font-size: 14px; color: #64748b;">Get back in quickly</p>
                        </td>
                        <td
                            style="text-align: center; padding: 15px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc;">
                            <div style="font-size: 22px; color: #667eea;">🛡️</div>
                            <p style="font-weight: 600; color: #1e293b; margin: 5px 0;">Account Protection</p>
                            <p style="font-size: 14px; color: #64748b;">Your security matters</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Security Tips -->
            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px;">
                <h3 style="color: #0369a1; margin-bottom: 12px;">Security Tips</h3>
                <ul style="color: #64748b; padding-left: 20px;">
                    <li>Choose a strong, unique password</li>
                    <li>Don't reuse passwords across different sites</li>
                    <li>Consider using a password manager</li>
                    <li>Never share your password with anyone</li>
                </ul>
            </div>

            <!-- Didn't Request Section -->
            <div
                style="text-align: center; margin-top: 25px; padding: 20px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px;">
                <h3 style="color: #dc2626; margin-bottom: 12px;">Didn't Request This?</h3>
                <p style="color: #64748b; margin-bottom: 0;">
                    If you didn't request a password reset, please ignore this email. Your account remains secure.
                    Consider changing your password if you're concerned about account security.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background: #f1f5f9; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
            <p style="color: #64748b; margin-bottom: 8px;">Need help? Email us at
                <a href="mailto:{{ $supportEmail }}"
                    style="color: #667eea; text-decoration: none; font-weight: 500;">{{ $supportEmail }}</a>
            </p>
            <p style="font-size: 13px; color: #94a3b8; margin-top: 10px;">
                © {{ $currentYear }} {{ $appName }}. All rights reserved.<br>
                This email was sent to {{ $user->email }}. Please do not reply to this automated message.
            </p>
        </div>
    </div>
</body>

</html>
