<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
        }

        .content {
            margin: 25px 0;
            font-size: 16px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
            text-align: center;
        }

        .notification-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .new-application {
            background: #d1ecf1;
            color: #0c5460;
        }

        .message-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }

        .action-button {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin: 15px 0;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
        }

        .details-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .details-table tr:last-child td {
            border-bottom: none;
        }

        .details-table .label {
            font-weight: 600;
            color: #495057;
            width: 30%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0; color: #2c3e50;">{{ $title }}</h1>
            @if (isset($type))
                <div class="notification-badge {{ $type }}">
                    {{ str_replace('_', ' ', ucfirst($type)) }}
                </div>
            @endif
        </div>

        <div class="content">
            <p style="margin-bottom: 20px;">Hello <strong>{{ $userName }}</strong>,</p>

            <div class="message-box">
                {!! nl2br(e($content)) !!}
            </div>

            @if (isset($data) && !empty($data))
                <h3 style="color: #2c3e50; margin-bottom: 15px;">Application Details:</h3>
                <table class="details-table">
                    @foreach ($data as $key => $value)
                        @if (is_string($value))
                            <tr>
                                <td class="label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</td>
                                <td><strong>{{ $value }}</strong></td>
                            </tr>
                        @endif
                    @endforeach
                    <tr>
                        <td class="label">Date Received:</td>
                        <td><strong>{{ $timestamp }}</strong></td>
                    </tr>
                </table>
            @endif

            {{-- @if (isset($data['job_vacancy_id']))
                <div style="text-align: center; margin: 25px 0;">
                    <a href="{{ url('/employer/job-applications/' . $data['job_vacancy_id']) }}" class="action-button"
                        style="color: white; text-decoration: none;">
                        View Application Details
                    </a>
                </div>
            @endif --}}
        </div>

        <div class="footer">
            <p style="margin: 0 0 10px 0;">
                This is an automated notification from <strong>{{ config('app.name', 'Job Portal') }}</strong>
            </p>
            <p style="margin: 0; font-size: 12px; color: #868e96;">
                Sent on {{ $timestamp }}
            </p>
        </div>
    </div>
</body>

</html>
