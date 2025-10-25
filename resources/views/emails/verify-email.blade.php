<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Verify Your Email - Job Portal</title>
</head>

<body
    style="font-family: Arial, Helvetica, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 0;">

    <div
        style="max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">

        <!-- Header -->
        <div
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center; color: white;">
            <h1 style="font-size: 26px; font-weight: 700; margin-bottom: 8px;">Welcome to JobPortal 🎉</h1>
            <p style="font-size: 16px; opacity: 0.9;">Let's get your account verified and ready to go!</p>
        </div>

        <!-- Body -->
        <div style="padding: 40px 30px;">
            <!-- Welcome Section -->
            <div style="text-align: center; margin-bottom: 30px;">
                <!-- Logo Image Only -->
                <div style="margin-bottom: 25px;">
                    <img src="https://yorpnyc.org.ph/images/clark-dark.png" alt="JobPortal"
                        style="height: 80px; width: auto;">
                </div>

                <!-- Content -->
                <h2 style="margin-bottom: 16px; color: #1e293b;">Verify Your Email Address</h2>
                <p style="color: #64748b; margin-bottom: 10px;">
                    Hello <strong>{{ $user->name }}</strong>, thank you for joining JobPortal!
                </p>
                <p style="color: #64748b; margin-bottom: 0;">
                    To complete your registration and unlock all features, please verify your email address.
                </p>
            </div>

            <!-- Verification Button -->
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ $verificationUrl }}"
                    style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; text-decoration: none; padding: 16px 32px; border-radius: 12px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                    Verify Email Address
                </a>
            </div>

            <!-- Expiry Notice -->
            <div
                style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 16px; margin: 20px 0; text-align: center; color: #856404;">
                <strong>⏰ Important:</strong> This verification link will expire in 24 hours.
            </div>

            <!-- Alternative Link -->
            <div style="text-align: center; margin-top: 20px;">
                <p style="color: #64748b; font-size: 14px; margin-bottom: 10px;">Or copy and paste this link in your
                    browser:</p>
                <div
                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; word-break: break-all;">
                    <a href="{{ $verificationUrl }}"
                        style="color: #667eea; text-decoration: none;">{{ $verificationUrl }}</a>
                </div>
            </div>

            <!-- Features -->
            <div style="margin: 30px 0;">
                <table width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td
                            style="text-align: center; padding: 15px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc;">
                            <div style="font-size: 22px; color: #667eea;">🔍</div>
                            <p style="font-weight: 600; color: #1e293b; margin: 5px 0;">Find Jobs</p>
                            <p style="font-size: 14px; color: #64748b;">Browse thousands of opportunities</p>
                        </td>
                        <td
                            style="text-align: center; padding: 15px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc;">
                            <div style="font-size: 22px; color: #667eea;">📊</div>
                            <p style="font-weight: 600; color: #1e293b; margin: 5px 0;">Track Applications</p>
                            <p style="font-size: 14px; color: #64748b;">Monitor your job progress</p>
                        </td>
                        <td
                            style="text-align: center; padding: 15px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc;">
                            <div style="font-size: 22px; color: #667eea;">💼</div>
                            <p style="font-weight: 600; color: #1e293b; margin: 5px 0;">Build Profile</p>
                            <p style="font-size: 14px; color: #64748b;">Showcase your skills</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Next Steps -->
            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px;">
                <h3 style="color: #0369a1; margin-bottom: 12px;">What's Next?</h3>
                <ul style="color: #64748b; padding-left: 20px;">
                    <li>Complete your professional profile</li>
                    <li>Upload your resume and portfolio</li>
                    <li>Set up job alerts for your preferences</li>
                    <li>Start applying to your dream jobs!</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div style="background: #f1f5f9; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
            <p style="color: #64748b; margin-bottom: 8px;">Need help? Email us at
                <a href="mailto:support@jobportal.com"
                    style="color: #667eea; text-decoration: none; font-weight: 500;">support@jobportal.com</a>
            </p>
            <p style="font-size: 13px; color: #94a3b8; margin-top: 10px;">
                © {{ date('Y') }} JobPortal. All rights reserved.<br>
                This email was sent to {{ $user->email }}. If you didn't create an account, please ignore this email.
            </p>
        </div>
    </div>
</body>

</html>
