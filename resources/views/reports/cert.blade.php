<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            margin: 40px 50px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .certification-title {
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px 12px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .no-column {
            width: 8%;
            text-align: center;
        }

        .name-column {
            width: 35%;
        }

        .position-column {
            width: 35%;
        }

        .date-column {
            width: 22%;
        }

        .footer-text {
            margin: 30px 0;
            text-align: justify;
        }

        .signature-section {
            margin-top: 50px;
        }

        .signature-line {
            display: inline-block;
            width: 35%;
            margin-top: 50px;
            margin-right: 10%;
        }

        .signature-line div {
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <p style="margin: 0; font-size: 25px;">CERTIFICATION OF EMPLOYMENT</p>
    </div>

    <!-- CERTIFICATION BODY -->
    <div class="certification-body">
        <p style="text-align: justify; margin-bottom: 25px;">
            This is to certify that the following Applicant/s were referred by Clark Development Corporation - CSR and
            Placement Division and have been hired at <b>{{ $job->title ?? '_________________________' }}</b> for the
            month of <b>{{ \Carbon\Carbon::createFromFormat('m', $filters['month'])->format('F') }}
                {{ $filters['year'] }}.</b>
        </p>

        <!-- APPLICANTS TABLE -->
        <table>
            <thead>
                <tr>
                    <th class="no-column">NO.</th>
                    <th class="name-column">NAME</th>
                    <th class="position-column">POSITION</th>
                    <th class="date-column">DATE HIRED</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $index => $app)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}.</td>
                        <td>{{ $app->jobSeeker->user->name ?? 'N/A' }}</td>
                        <td>{{ $app->jobVacancy->title ?? 'N/A' }}</td>
                        <td>
                            @php
                                $hiredTransaction = $app->jobApplicationTransactions->firstWhere('status', 'hired');
                            @endphp

                            @if ($hiredTransaction)
                                {{ \Carbon\Carbon::parse($hiredTransaction->finalized_date)->format('m/d/Y') }}
                            @else
                                {{ $app->created_at ? $app->created_at->format('m/d/Y') : 'N/A' }}
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">No applicants found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- FOOTER TEXT -->
        <div class="footer-text">
            <p>This certificate has been issued upon request of the interested party for reference purposes.</p>
        </div>

        <!-- SIGNATURE SECTION -->
        <div class="signature-section">
            <p style="margin-bottom: 40px; font-weight: bold;">Certified True and Correct:</p>

            <div class="signature-line">
                <div>Signature Over Printed Name</div>
            </div>

            <div class="signature-line">
                <div>Position/Designation</div>
            </div>
        </div>
    </div>

</body>

</html>
