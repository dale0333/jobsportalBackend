<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Password Reset Successful - {{ $appName }}</title>
</head>

<body
    style="font-family: Arial, Helvetica, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 0;">
    <div
        style="max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">
        <!-- Header -->
        <div
            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 40px 30px; text-align: center; color: white;">
            <h1 style="font-size: 26px; font-weight: 700; margin-bottom: 8px;">Password Reset Successful!</h1>
            <p style="font-size: 16px; opacity: 0.9;">Your account security has been updated</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px; text-align: center;">
            <div style="margin-bottom: 25px;">
                <img src="https://yorpnyc.org.ph/images/clark-dark.png" alt="{{ $appName }}"
                    style="height: 80px; width: auto;">
            </div>

            <h2 style="margin-bottom: 16px; color: #1e293b;">Password Updated Successfully</h2>
            <p style="color: #64748b; margin-bottom: 10px;">
                Hello <strong>{{ $user->name }}</strong>, your password has been reset successfully.
            </p>
            <p style="color: #64748b; margin-bottom: 25px;">
                You can now log in to your account using your new password.
            </p>

            <!-- Login Button -->
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $loginUrl }}"
                    style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; text-decoration: none; padding: 16px 32px; border-radius: 12px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">
                    Login to Your Account
                </a>
            </div>

            <!-- Security Notice -->
            <div
                style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; margin-top: 25px;">
                <h3 style="color: #0369a1; margin-bottom: 12px;">Security Notice</h3>
                <p style="color: #64748b; margin-bottom: 0;">
                    If you did not request this password reset, please contact our support team immediately at
                    <a href="mailto:{{ $supportEmail }}" style="color: #667eea; text-decoration: none;">
                        {{ $supportEmail }}
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background: #f1f5f9; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
            <p style="color: #64748b; margin-bottom: 8px;">Need help? Contact us at
                <a href="mailto:{{ $supportEmail }}"
                    style="color: #667eea; text-decoration: none; font-weight: 500;">{{ $supportEmail }}</a>
            </p>
            <p style="font-size: 13px; color: #94a3b8; margin-top: 10px;">
                © {{ $currentYear }} {{ $appName }}. All rights reserved.<br>
                This is a security notification for your account protection.
            </p>
        </div>
    </div>
</body>

</html>
