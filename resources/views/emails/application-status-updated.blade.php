<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: Arial, Helvetica, sans-serif;
            color: #334155;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        /* HEADER */
        .header {
            background: #667eea;
            color: #ffffff;
            padding: 30px 25px;
            text-align: left;
        }

        .header h1 {
            margin: 0 0 8px;
            font-size: 22px;
        }

        .header p {
            margin: 0;
            font-size: 14px;
            opacity: 0.95;
        }

        /* BODY */
        .body-content {
            padding: 25px;
        }

        .logo img {
            height: 45px;
            margin-bottom: 20px;
        }

        .greeting h2 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #1e293b;
        }

        .greeting p {
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }

        /* MESSAGE BOX */
        .message-box {
            padding: 2px 20px;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #667eea;
            border-radius: 10px;
            background: #ffffff;
        }

        .main-message {
            font-size: 15px;
            line-height: 1.7;
            color: #1e293b;
        }

        .status-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .status-badge {
            display: inline-block;
            margin-top: 6px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 999px;
            text-transform: capitalize;
        }

        /* STATUS COLORS */
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-invited {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-interview {
            background: #e0f2fe;
            color: #0369a1;
        }

        .status-rejected {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-hired {
            background: #dcfce7;
            color: #166534;
        }

        .status-withdrawn {
            background: #f1f5f9;
            color: #475569;
        }

        /* DETAILS */
        .details-section {
            margin-top: 25px;
        }

        .details-section h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #1e293b;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .details-table td {
            padding: 12px 14px;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        .details-table tr:last-child td {
            border-bottom: none;
        }

        .label {
            background: #f9fafb;
            font-weight: 600;
            width: 40%;
        }

        /* BUTTON */
        .action-section {
            text-align: center;
            margin: 30px 0 10px;
        }

        .action-button {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
        }

        /* FOOTER */
        .footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #64748b;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
        }

        .footer small {
            display: block;
            margin-top: 10px;
            font-size: 12px;
            color: #94a3b8;
        }

        /* MOBILE */
        @media (max-width: 600px) {
            .body-content {
                padding: 20px;
            }

            .details-table td {
                display: block;
                width: 100%;
            }

            .label {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- HEADER -->
        <div class="header">
            <h1>{{ $title }}</h1>
            <p>Update regarding your job application</p>
        </div>

        <!-- BODY -->
        <div class="body-content">

            <!-- LOGO -->
            <div class="logo">
                <img src="https://yorpnyc.org.ph/images/clark-dark.png" alt="{{ config('app.name', 'Job Portal') }}">
            </div>

            <!-- GREETING -->
            <div class="greeting">
                <h2>Hello {{ $userName }},</h2>
                <p>Here’s the latest update on your application.</p>
            </div>

            <!-- MESSAGE -->
            @if (!empty($content))
                <div class="message-box">
                    <p class="main-message">
                        {!! nl2br(e(trim($content))) !!}
                    </p>
                </div>
            @endif

            <!-- DETAILS -->
            @if (isset($data) && !empty($data))
                <div class="details-section">
                    <h3>Application Details</h3>

                    <table class="details-table">
                        @foreach ($data as $key => $value)
                            @if (is_string($value) && !empty($value) && !in_array($key, ['application_id', 'action_type']))
                                <tr>
                                    <td class="label">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                    <td>{{ $value }}</td>
                                </tr>
                            @endif
                        @endforeach

                        <tr>
                            <td class="label">Notification Date</td>
                            <td>{{ $timestamp }}</td>
                        </tr>
                    </table>
                </div>
            @endif

            <!-- CTA -->
            <div class="action-section">
                <a href="https://cdc-jobsportal.itwattsavers.com" class="action-button">
                    Login Now
                </a>
            </div>

        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p>
                Need help? Contact
                <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
            </p>

            <small>
                © {{ date('Y') }} {{ config('app.name', 'Job Portal') }}<br>
                Sent to {{ $userEmail }} • Do not reply to this email
            </small>
        </div>

    </div>
</body>

</html>
