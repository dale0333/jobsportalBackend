<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Employment Report</title>
    <style>
        @page {
            margin: 40px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            font-size: 12px;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }

        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: normal;
        }

        .report-info {
            margin-bottom: 15px;
            text-align: center;
        }

        .report-info span {
            margin: 0 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        td {
            font-size: 9px;
        }

        .industry-total {
            font-weight: bold;
            background-color: #e8e8e8;
        }

        .grand-total {
            font-weight: bold;
            background-color: #d4d4d4;
            border-top: 2px solid #000;
        }

        .footer {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px solid #333;
            font-size: 9px;
        }

        .legends,
        .notes {
            font-size: 9px;
        }

        .legends h4,
        .notes h4 {
            margin: 0 0 5px 0;
            font-size: 10px;
        }

        .notes ul {
            margin: 0;
            padding-left: 15px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .header img.left-logo {
            position: absolute;
            left: 0;
            top: 0;
            height: 50px;
            width: auto;
        }

        .header img.right-logo {
            position: absolute;
            right: 0;
            top: 0;
            height: 50px;
            width: auto;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <div class="header">
        <img src="{{ public_path('images/logo2.png') }}" class="left-logo" alt="Left Logo">
        <img src="{{ public_path('images/logo1.png') }}" class="right-logo" alt="Right Logo">

        <h1>CSR AND PLACEMENT DIVISION</h1>
        <h2>EMPLOYMENT REPORT</h2>
        <div class="report-info">
            @if (!empty($filters['date_start']) && !empty($filters['date_end']))
                <span>Period: {{ \Carbon\Carbon::parse($filters['date_start'])->format('F d, Y') }} to
                    {{ \Carbon\Carbon::parse($filters['date_end'])->format('F d, Y') }}</span>
            @else
                <span>Report Date: {{ \Carbon\Carbon::now()->format('F d, Y') }}</span>
            @endif

            @if (!empty($filters['employer']))
                <span>Employer:
                    {{ \App\Models\Employer::find($filters['employer'])->user->name ?? 'All Employers' }}</span>
            @endif
        </div>
    </div>

    <!-- EMPLOYMENT TABLE -->
    <table>
        <thead>
            <tr>
                <th>LOC. NO</th>
                <th>COMPANY</th>
                <th>PROJECT INDUSTRY</th>
                <th>TOTAL DIRECT</th>
                <th>TOTAL INDIRECT</th>
                <th>TOTAL EXPAT</th>
                <th>TOTAL</th>
                <th>REMARKS</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedData = [];
                $industryTotals = [];
                $grandTotals = ['direct' => 0, 'indirect' => 0, 'expat' => 0, 'total' => 0];

                foreach ($records as $employer) {
                    if ($employer && $employer->user) {
                        $industry = $employer->industry ?? 'Other';
                        $locNo = $employer->locator_number ?? 'N/A';

                        if (!isset($groupedData[$industry][$employer->id])) {
                            $groupedData[$industry][$employer->id] = [
                                'loc_no' => $locNo,
                                'company' => $employer->user->name,
                                'industry' => $industry,
                                'direct' => 0,
                                'indirect' => 0,
                                'expat' => 0,
                                'total' => 0,
                            ];
                        }

                        if (!isset($industryTotals[$industry])) {
                            $industryTotals[$industry] = ['direct' => 0, 'indirect' => 0, 'expat' => 0, 'total' => 0];
                        }

                        foreach ($employer->jobVacancies as $vacancy) {
                            $directCount = $vacancy->jobApplications->where('type', 'applied')->count();
                            $indirectCount = $vacancy->jobApplications->where('type', 'invited')->count();
                            $expatCount = $vacancy->jobApplications->where('type', 'matched')->count();
                            $totalCount = $directCount + $indirectCount + $expatCount;

                            $groupedData[$industry][$employer->id]['direct'] += $directCount;
                            $groupedData[$industry][$employer->id]['indirect'] += $indirectCount;
                            $groupedData[$industry][$employer->id]['expat'] += $expatCount;
                            $groupedData[$industry][$employer->id]['total'] += $totalCount;

                            $industryTotals[$industry]['direct'] += $directCount;
                            $industryTotals[$industry]['indirect'] += $indirectCount;
                            $industryTotals[$industry]['expat'] += $expatCount;
                            $industryTotals[$industry]['total'] += $totalCount;

                            $grandTotals['direct'] += $directCount;
                            $grandTotals['indirect'] += $indirectCount;
                            $grandTotals['expat'] += $expatCount;
                            $grandTotals['total'] += $totalCount;
                        }
                    }
                }

                ksort($groupedData);
                ksort($industryTotals);
            @endphp

            @foreach ($groupedData as $industry => $employers)
                @foreach ($employers as $data)
                    <tr>
                        <td class="text-center">{{ $data['loc_no'] }}</td>
                        <td>{{ $data['company'] }}</td>
                        <td>{{ $data['industry'] }}</td>
                        <td class="text-center">{{ $data['direct'] }}</td>
                        <td class="text-center">{{ $data['indirect'] }}</td>
                        <td class="text-center">{{ $data['expat'] }}</td>
                        <td class="text-center">{{ $data['total'] }}</td>
                        <td class="text-center">*</td>
                    </tr>
                @endforeach

                @if (isset($industryTotals[$industry]))
                    <tr class="industry-total">
                        <td colspan="3" class="text-right"><strong>TOTAL {{ strtoupper($industry) }}</strong></td>
                        <td class="text-center"><strong>{{ $industryTotals[$industry]['direct'] }}</strong></td>
                        <td class="text-center"><strong>{{ $industryTotals[$industry]['indirect'] }}</strong></td>
                        <td class="text-center"><strong>{{ $industryTotals[$industry]['expat'] }}</strong></td>
                        <td class="text-center"><strong>{{ $industryTotals[$industry]['total'] }}</strong></td>
                        <td class="text-center"><strong>-</strong></td>
                    </tr>
                @endif
            @endforeach

            <tr class="grand-total">
                <td colspan="3" class="text-right"><strong>GRAND TOTAL</strong></td>
                <td class="text-center"><strong>{{ $grandTotals['direct'] }}</strong></td>
                <td class="text-center"><strong>{{ $grandTotals['indirect'] }}</strong></td>
                <td class="text-center"><strong>{{ $grandTotals['expat'] }}</strong></td>
                <td class="text-center"><strong>{{ $grandTotals['total'] }}</strong></td>
                <td class="text-center"><strong>-</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- LEGENDS AND NOTES -->
    <table>
        <tr>
            <td style="border: none; width: 40%">
                <div class="legends">
                    <h4>LEGENDS</h4>
                    <p>* Updated</p>
                    <p>** Not Updated</p>
                    <p>^ Not Registered in the Jobs Portal</p>
                </div>
            </td>
            <td style="border: none">
                <div class="notes">
                    <h4>NOTES:</h4>
                    <ul>
                        <li>HANN PHILIPPINES INC. include Clark Marriott and Swissotel Clark Phils. Inc.</li>
                        <li>DONGGWANG CLARK CORPORATION DOING BUSINESS UNDER THE NAME AND STYLE OF SUN VALLEY COUNTRY
                            CLUB includes Hilton Clark Sun Valley Resort</li>
                        <li>PREMIER CENTRAL INC. includes employment of TENANTS added at the INDIRECT column.</li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div class="footer text-center">
        Generated from System on {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}
    </div>
</body>

</html>
