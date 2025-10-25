<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .email-body {
            padding: 40px 30px;
        }

        .success-icon {
            text-align: center;
            margin-bottom: 25px;
        }

        .success-icon .icon {
            background-color: #10b981;
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
        }

        .info-section {
            background-color: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 5px;
            margin: 25px 0;
        }

        .info-item {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #4b5563;
            min-width: 120px;
        }

        .info-value {
            color: #1f2937;
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .email-footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }

        .timestamp {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 10px;
        }

        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 25px 0;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>✅ SMTP Test Successful</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="success-icon">
                <div class="icon">✓</div>
            </div>

            <h2 style="text-align: center; color: #1f2937; margin-bottom: 10px;">
                Email Configuration Test
            </h2>
            <p style="text-align: center; color: #6b7280; margin-bottom: 30px;">
                This test email confirms that your SMTP configuration is working correctly.
            </p>

            <div class="info-section">
                <h3 style="color: #1f2937; margin-top: 0; margin-bottom: 20px;">
                    📧 Email Details
                </h3>

                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status-badge">Delivered Successfully</span>
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">SMTP Server:</span>
                    <span class="info-value">{{ $smtpName ?? 'Not specified' }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">Subject:</span>
                    <span class="info-value">{{ $title }}</span>
                </div>

                <div class="info-item">
                    <span class="info-label">Sent At:</span>
                    <span class="info-value">{{ $timestamp ?? now()->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>

            <div class="divider"></div>

            <div style="text-align: center; color: #6b7280;">
                <p style="margin-bottom: 15px;">
                    <strong>What this means:</strong>
                </p>
                <p style="margin: 0; font-size: 14px;">
                    Your email settings are properly configured and ready to send notifications,
                    alerts, and other communications from your application.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p style="margin: 0;">
                This is an automated test email from your application.
            </p>
            <div class="timestamp">
                Generated on {{ $timestamp ?? now()->format('Y-m-d H:i:s') }}
            </div>
        </div>
    </div>
</body>

</html>
