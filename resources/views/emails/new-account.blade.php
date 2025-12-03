<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Account Information - {{ $appName }}</title>
</head>

<body
    style="font-family: Arial, Helvetica, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 0;">
    <div
        style="max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">

        <!-- Header -->
        <div
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center; color: white;">
            <h1 style="font-size: 26px; font-weight: 700; margin-bottom: 8px;">Welcome to {{ $appName }}! 🎉</h1>
            <p style="font-size: 16px; opacity: 0.9;">Your account has been successfully created</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">
            <!-- Welcome Section -->
            <div style="text-align: center; margin-bottom: 30px;">
                <!-- Logo -->
                <div style="margin-bottom: 25px;">
                    <img src="https://yorpnyc.org.ph/images/clark-dark.png" alt="{{ $appName }}"
                        style="height: 80px; width: auto;">
                </div>

                <!-- Content -->
                <h2 style="margin-bottom: 16px; color: #1e293b;">Your Account is Ready!</h2>
                <p style="color: #64748b; margin-bottom: 10px;">
                    Hello <strong>{{ $user->name }}</strong>, welcome to {{ $appName }}!
                </p>
                <p style="color: #64748b; margin-bottom: 0;">
                    Below are your login credentials to access your account:
                </p>
            </div>

            <!-- Credentials Card -->
            <div
                style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 25px; margin: 25px 0;">
                <h3 style="color: #0369a1; margin-bottom: 20px; text-align: center;">Your Account Credentials</h3>

                <div style="margin-bottom: 15px;">
                    <div style="color: #64748b; font-size: 14px; margin-bottom: 5px;">Email Address</div>
                    <div
                        style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-weight: 600; color: #1e293b;">
                        {{ $user->email }}
                    </div>
                </div>

                <div style="margin-bottom: 0;">
                    <div style="color: #64748b; font-size: 14px; margin-bottom: 5px;">Your Password</div>
                    <div
                        style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-weight: 600; color: #1e293b; letter-spacing: 1px;">
                        {{ $texPast }}
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div
                style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 16px; margin: 20px 0; color: #92400e;">
                <div style="display: flex; align-items: flex-start;">
                    <div style="font-size: 18px; margin-right: 10px;">⚠️</div>
                    <div>
                        <strong>Security Notice:</strong> For your security, please log in and change your password
                        immediately after your first login.
                    </div>
                </div>
            </div>

            <!-- Login Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $loginUrl }}"
                    style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; text-decoration: none; padding: 16px 32px; border-radius: 12px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">
                    Login to Your Account
                </a>
            </div>

            <!-- Quick Steps -->
            <div
                style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; margin: 25px 0;">
                <h3 style="color: #0369a1; margin-bottom: 15px;">Next Steps</h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; align-items: flex-start;">
                        <div
                            style="background: #10b981; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; margin-right: 12px; flex-shrink: 0;">
                            1</div>
                        <div>
                            <strong>Login with your new password</strong><br>
                            <span style="color: #64748b; font-size: 14px;">Use the credentials provided above</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: flex-start;">
                        <div
                            style="background: #10b981; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; margin-right: 12px; flex-shrink: 0;">
                            2</div>
                        <div>
                            <strong>Change your password</strong><br>
                            <span style="color: #64748b; font-size: 14px;">Go to account settings to set a new secure
                                password</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Section -->
            <div style="text-align: center; margin-top: 30px;">
                <p style="color: #64748b; margin-bottom: 15px;">
                    If you have any questions or need assistance, our support team is here to help.
                </p>
                <a href="mailto:{{ $supportEmail }}" style="color: #10b981; text-decoration: none; font-weight: 500;">
                    {{ $supportEmail }}
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background: #f1f5f9; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
            <p style="color: #64748b; margin-bottom: 8px;">
                This is an automated message from {{ $appName }}
            </p>
            <p style="font-size: 13px; color: #94a3b8; margin-top: 10px;">
                © {{ $currentYear }} {{ $appName }}. All rights reserved.<br>
                This email was sent to {{ $user->email }}. Please do not reply to this email.
            </p>

            <!-- Security Footer -->
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                <p style="font-size: 12px; color: #94a3b8;">
                    For security reasons, please do not share this email with anyone.<br>
                    If you didn't request a password reset, please contact support immediately.
                </p>
            </div>
        </div>
    </div>
</body>

</html>
