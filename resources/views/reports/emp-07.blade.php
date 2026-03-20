<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            position: relative;
            padding-top: 10px;
        }

        .header img.left-logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 100px;
        }

        .header img.right-logo {
            position: absolute;
            right: 0;
            top: 0;
            width: 100px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin-top: 20px;
            font-size: 9px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
            padding: 1px
        }

        .position-title {
            font-size: 10px;
            font-weight: bold;
            padding: 6px;
            background-color: #f0f0f0;
        }

        .footer-section {
            margin-top: 20px;
        }

        .text-center {
            text-align: center;
        }

        .signature-line {
            font-size: 12px;
            text-align: center;
            line-height: 1.3;
        }

        .privacy-notice {
            font-size: 7px;
            font-weight: bold;
            text-align: justify;
            width: 70%;
            border: 1px solid #000;
            padding: 5px;
            margin: 0;
        }

        .company-info table td {
            border: none !important;
            padding: 3px 0;
            font-size: 10px;
            vertical-align: top;
        }

        .contact-info {
            text-align: center;
            font-size: 9px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    @foreach ($records as $job)
        <!-- HEADER -->
        <div class="header">
            <img src="{{ public_path('images/logo2.png') }}" class="left-logo">
            <img src="{{ public_path('images/logo1.png') }}" class="right-logo">
            <p style="margin: 0; font-size: 12px; font-weight: bold;">CSR AND PLACEMENT DIVISION</p>
            <p style="margin: 0; font-size: 12px; font-weight: bold;">LIST OF REFERRED APPLICANTS</p>
        </div>

        <table>

            <tr>
                <td colspan="8" class="position-title">
                    FOR THE POSITION OF: {{ $job->title ?? '_________________________' }}
                </td>
            </tr>

            <thead>
                <tr>
                    <th style="width: 5%;" rowspan="2">NO.</th>
                    <th style="width: 25%;" rowspan="2">NAME</th>
                    <th style="width: 20%;" rowspan="2">CONTACT NOS.<br>(Telephone/Mobile Phone)</th>
                    <th style="width: 25%;" rowspan="2">EDUCATION / ATTACHMENT / COURSE</th>
                    <th colspan="4" style="width: 30%;">REMARKS (to be filled-out by CFZ Locator)</th>
                </tr>
                <tr>
                    <th style="width: 7.5%; font-size: 6px;">Hired</th>
                    <th style="width: 7.5%; font-size: 6px;">Not Qualified</th>
                    <th style="width: 7.5%; font-size: 6px;">For Further Evaluation</th>
                    <th style="width: 7.5%; font-size: 6px;">Notified But Non Appearance</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($job->jobApplications as $app)
                    <tr>
                        <td style="text-align:center;">{{ $loop->iteration }}</td>
                        <td>{{ $app->jobSeeker->user->name ?? 'N/A' }}</td>
                        <td>{{ $app->jobSeeker->user->telephone ?? 'N/A' }}</td>
                        <td>
                            {{ $app->jobSeeker->education_level ?? '' }}
                            {{ $app->jobSeeker->field_of_study ? ' - ' . $app->jobSeeker->field_of_study : '' }}
                        </td>
                        <td style="text-align:center;">{{ $app->status == 2 ? 'X' : '' }}</td>
                        <td style="text-align:center;">{{ $app->status == 3 ? 'X' : '' }}</td>
                        <td style="text-align:center;">{{ $app->status == 4 ? 'X' : '' }}</td>
                        <td style="text-align:center;">{{ $app->status == 5 ? 'X' : '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;">No applicants found.</td>
                    </tr>
                @endforelse

            </tbody>

            <tr>
                <td colspan="8">
                    <p style="margin:10px 0 0 0;font-size:10px;font-weight:bold;">Endorsed by:</p>
                    <p style="margin:10px 0 0 0;font-size:10px;font-weight:bold;">Skills and Placement Assistant:</p>
                </td>
            </tr>

        </table>


        <!-- PRIVACY NOTICE -->
        <div class="footer-section text-center">
            <p style="font-size:9px;font-weight:bold;margin:0;">
                *Subject to the Privacy Sharing Agreement of CFZ Locators and Applicant, all personal data of the
                applicant
                shall be used only by the addressee company to process the application and possible employment of said
                applicant and not for any other purposes not agreed upon.
            </p>
        </div>


        <!-- COMPANY INFORMATION -->
        <div class="company-info footer-section">
            <table width="100%">
                <tr>
                    <td>
                        <strong style="display:inline-block;width:35%;">COMPANY NAME:</strong>
                        <span>{{ $job->employer->user->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <strong style="display:inline-block;width:40%;">DATE OF REQUEST:</strong>
                        <span>{{ now()->format('m/d/Y') }}</span>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong style="display:inline-block;width:35%;">CONTACT NOS.:</strong>
                        <span>{{ $job->employer->user->telephone ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <strong style="display:inline-block;width:40%;">TO BE RETURNED ON:</strong>
                        <span>{{ optional($job->deadline)->format('m/d/Y') }}</span>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong style="display:inline-block;width:35%;">EMAIL ADD.:</strong>
                        <span>{{ $job->employer->user->email ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <strong style="display:inline-block;width:40%;">DATE RETURNED:</strong>
                        <span>____________________________</span>
                    </td>
                </tr>
            </table>
        </div>


        <!-- UNDERTAKING -->
        <div class="footer-section text-center">
            <p style="font-size:9px;font-weight:bold;margin:0;">
                I hereby undertake to endorse the following job applications to CDC and submit status report on the
                remarks
                below for a maximum period of fifteen (15) working days only.
            </p>
        </div>


        <!-- SIGNATURE AREA -->
        <table width="100%" style="margin-top:20px;">
            <tr>
                <td width="60%" style="vertical-align:top;padding:5px;border:none;">
                    <div class="signature-line">
                        Prepared by: ____________________________<br>
                        Company's Representative<br>
                        Printed Name and Signature / Position
                    </div>
                </td>

                <td width="40%" style="vertical-align:top;padding:5px;border:none;">
                    <div class="privacy-notice">
                        *Failure to return Form FM-CDC-CSRPD-07 shall temporarily refrain you from accessing services
                        provided by the skills and placement section until compliance has been satisfactorily completed.
                    </div>
                </td>
            </tr>
        </table>


        <!-- CONTACT INFO -->
        <div class="footer-section text-center">
            <p style="font-size:9px;font-weight:bold;margin:0;">
                For more inquiries, you may call Telephone Nos. (045) 499-2265<br>
                Email: jobsatclark@gmail.com
            </p>
        </div>


        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach

</body>

</html>
