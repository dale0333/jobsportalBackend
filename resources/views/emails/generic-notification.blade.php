<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
            margin-top: 0;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .notification-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 15px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .body-content {
            padding: 40px 30px;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            margin-bottom: 25px;
        }

        .logo img {
            height: 60px;
            width: auto;
        }

        .greeting {
            margin-bottom: 25px;
        }

        .message-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }

        .details-section {
            margin: 30px 0;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .details-table td {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .details-table tr:last-child td {
            border-bottom: none;
        }

        .details-table .label {
            font-weight: 600;
            color: #475569;
            width: 35%;
            background: #f1f5f9;
        }

        .action-section {
            text-align: center;
            margin: 30px 0;
        }

        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .features-grid {
            margin: 30px 0;
        }

        .features-table {
            width: 100%;
            border-collapse: collapse;
        }

        .features-table td {
            text-align: center;
            padding: 20px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
        }

        .feature-icon {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 10px;
        }

        .feature-title {
            font-weight: 600;
            color: #1e293b;
            margin: 8px 0 5px 0;
        }

        .feature-desc {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }

        .info-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }

        .info-box h3 {
            color: #0369a1;
            margin-bottom: 15px;
            margin-top: 0;
        }

        .info-box ul {
            color: #64748b;
            padding-left: 20px;
            margin-bottom: 0;
        }

        .info-box li {
            margin-bottom: 8px;
        }

        .warning-box {
            text-align: center;
            margin-top: 25px;
            padding: 25px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
        }

        .warning-box h3 {
            color: #dc2626;
            margin-bottom: 12px;
            margin-top: 0;
        }

        .warning-box p {
            color: #64748b;
            margin-bottom: 0;
        }

        .footer {
            background: #f1f5f9;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            color: #64748b;
            margin-bottom: 8px;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .footer small {
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.5;
        }

        @media (max-width: 600px) {
            .body-content {
                padding: 30px 20px;
            }

            .header {
                padding: 30px 20px;
            }

            .details-table td {
                padding: 12px 15px;
                display: block;
                width: 100%;
                border-bottom: 1px solid #e2e8f0;
            }

            .details-table .label {
                width: 100%;
                background: #f1f5f9;
                font-weight: 600;
                border-bottom: none;
            }

            .features-table {
                display: block;
            }

            .features-table td {
                display: block;
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $title }}</h1>
            <p>Important notification regarding your application</p>
            @if (isset($type))
                <div class="notification-badge">
                    {{ str_replace('_', ' ', ucfirst($type)) }}
                </div>
            @endif
        </div>

        <!-- Body Content -->
        <div class="body-content">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <!-- Logo -->
                <div class="logo">
                    <img src="https://yorpnyc.org.ph/images/clark-dark.png"
                        alt="{{ config('app.name', 'Job Portal') }}">
                </div>

                <!-- Greeting -->
                <div class="greeting">
                    <h2 style="margin-bottom: 16px; color: #1e293b;">Hello {{ $userName }},</h2>
                    <p style="color: #64748b; margin-bottom: 0;">
                        We have an important update regarding your application.
                    </p>
                </div>
            </div>

            <!-- Message Content -->
            <div class="message-box">
                <div style="font-size: 18px; color: #1e293b; margin-bottom: 15px;">
                    {!! nl2br(e($content)) !!}
                </div>
            </div>

            <!-- Application Details -->
            @if (isset($data) && !empty($data))
                <div class="details-section">
                    <h3 style="color: #1e293b; margin-bottom: 20px; text-align: center;">Application Details</h3>
                    <table class="details-table">
                        @foreach ($data as $key => $value)
                            @if (is_string($value) && !empty($value))
                                <tr>
                                    <td class="label">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                    <td><strong>{{ $value }}</strong></td>
                                </tr>
                            @endif
                        @endforeach
                        <tr>
                            <td class="label">Date Received</td>
                            <td><strong>{{ $timestamp }}</strong></td>
                        </tr>
                    </table>
                </div>
            @endif

            <!-- Action Button -->
            @if (isset($data['job_vacancy_id']))
                <div class="action-section">
                    <a href="{{ url('/employer/job-applications/' . $data['job_vacancy_id']) }}" class="action-button">
                        View Application Details
                    </a>
                </div>
            @endif

            <!-- Features Grid -->
            <div class="features-grid">
                <table class="features-table" cellspacing="15">
                    <tr>
                        <td>
                            <div class="feature-icon">📋</div>
                            <p class="feature-title">Application Tracking</p>
                            <p class="feature-desc">Monitor your application status</p>
                        </td>
                        <td>
                            <div class="feature-icon">⚡</div>
                            <p class="feature-title">Quick Updates</p>
                            <p class="feature-desc">Real-time status changes</p>
                        </td>
                        <td>
                            <div class="feature-icon">🛡️</div>
                            <p class="feature-title">Secure Process</p>
                            <p class="feature-desc">Your data is protected</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Additional Information -->
            <div class="info-box">
                <h3>Next Steps</h3>
                <ul>
                    <li>Keep an eye on your email for further updates</li>
                    <li>Ensure your contact information is up to date</li>
                    <li>Prepare for potential interviews or assessments</li>
                    <li>Check your spam folder if you're expecting updates</li>
                </ul>
            </div>

            <!-- Didn't Apply Section -->
            <div class="warning-box">
                <h3>Not Expecting This?</h3>
                <p>
                    If you didn't submit this application or believe this was sent in error,
                    please contact our support team immediately. Your account security is important to us.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Need help? Email us at
                <a href="mailto:{{ $supportEmail ?? 'support@example.com' }}">
                    {{ $supportEmail ?? 'support@example.com' }}
                </a>
            </p>
            <small>
                © {{ date('Y') }} {{ config('app.name', 'Job Portal') }}. All rights reserved.<br>
                This email was sent to {{ $userEmail ?? 'you' }}. Please do not reply to this automated message.
            </small>
        </div>
    </div>
</body>

</html>
