<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            position: relative;
        }

        .header img.left-logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 120px;
        }

        .header img.right-logo {
            position: absolute;
            right: 0;
            top: 0;
            width: 120px;
        }

        .header-title {
            margin: 0;
            margin-bottom: 10px;
            font-size: 18px;
            text-transform: uppercase;
        }

        .sub-title {
            margin: 2px 0 0 0;
            font-size: 18px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 11px;
        }

        .no-border td {
            border: none !important;
        }

        .header-table th,
        .header-table td {
            text-align: left;
            padding: 6px;
        }

        .section-title {
            background: #eaeaea;
            text-align: center;
            padding: 4px;
            border: 1px solid #000;
        }

        .category {
            background: #f0f0f0;
            text-transform: uppercase;
        }

        .text-center {
            text-align: center;
        }


        .notes-section {
            margin-top: 10px;
            font-size: 12px;
        }

        .notes-section ol {
            margin-top: 1px;
            margin-bottom: 20px;
        }

        .notes-section li {
            margin-bottom: 1px;
        }

        .certification-statement {
            font-weight: bold;
            font-size: 9px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <img src="{{ public_path('images/logo2.png') }}" class="left-logo" alt="Left Logo">
        <img src="{{ public_path('images/InvestPH.png') }}" class="right-logo" alt="Right Logo">

        <p class="header-title">
            CSR AND PLACEMENT DIVISION
            <br> REPORT ON EMPLOYMENT
        </p>
    </div>

    <!-- HEADER INFORMATION -->
    <table class="header-table">
        <tr>
            <td style="width: 30%">MONTH:</td>
            <td style="width: 45%">
                {{ \Carbon\Carbon::createFromFormat('m', $filters['month'])->format('F') }}</td>
            <td>Year:</td>
            <td>{{ $filters['year'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>NAME OF ENTERPRISE:</td>
            <td colspan="3">{{ $title }}</td>
        </tr>
        <tr>
            <td>NAME OF AGENCY / CONTRACTOR:</td>
            <td colspan="3">N/A</td>
        </tr>
    </table>

    <br>

    <!-- CATEGORY BREAKDOWN TABLE -->
    <table>
        <tbody>
            <tr>
                <td rowspan="3" colspan="2">BREAKDOWN OF EMPLOYEES BY CATEGORY</td>
                <td colspan="7" class="text-center">LOCAL / FILIPINO EMPLOYEES</td>
            </tr>
            <tr>
                <td rowspan="2" class="text-center" style="width: 9%;">DIRECT</td>
                <td colspan="5" class="text-center">INDIRECT</td>
                <td rowspan="2" class="text-center" style="width: 9%;">TOTAL</td>
            </tr>
            <tr>
                <td class="text-center" style="width: 9%;">Security</td>
                <td class="text-center" style="width: 9%;">Janitorial</td>
                <td class="text-center" style="width: 9%;">Ground</td>
                <td class="text-center" style="width: 9%;">Construction</td>
                <td class="text-center" style="width: 9%;">Others</td>
            </tr>

            @php
                $records = collect($records->details ?? [])
                    ->filter(function ($item) {
                        return $item->nationality == 'Filipino';
                    })
                    ->values();

                $indirectCategories = ['Security', 'Janitorial', 'Ground', 'Construction', 'Others'];
                $categories = array_merge(['Direct'], $indirectCategories);
                $genders = ['Male', 'Female'];
                $statuses = ['Regular', 'Probationary', 'Casual'];
                $directs = ['AVP and up', 'Managerial', 'Supervisory', 'Rank and File'];
                $residences = [
                    'Angeles',
                    'Mabalacat',
                    'Porac',
                    'Other Pampanga',
                    'Bamban',
                    'Capas',
                    'Other Tarlac',
                    'Others',
                ];

                $directCounts = [];
                foreach ($directs as $direct) {
                    $directCounts[$direct] = $records->where('category', $direct)->count();
                }

                $directIndirectCounts = [];
                foreach ($indirectCategories as $cat) {
                    $directIndirectCounts[$cat] = $records->where('category', $cat)->count();
                }

                $directGrandTotal = $records->count();

                $genderCounts = [];
                $genderDirectTotal = 0;
                foreach ($genders as $gender) {
                    foreach ($categories as $cat) {
                        if ($cat === 'Direct') {
                            $genderCounts[$gender][$cat] = $records
                                ->where('gender', $gender)
                                ->whereNotIn('category', $indirectCategories)
                                ->count();
                        } else {
                            $genderCounts[$gender][$cat] = $records
                                ->where('gender', $gender)
                                ->where('category', $cat)
                                ->count();
                        }
                    }
                    $genderCounts[$gender]['Total'] = $records
                        ->where('gender', $gender)
                        ->whereIn('category', $categories)
                        ->count();
                    $genderDirectTotal += $genderCounts[$gender]['Total'];
                }

                $statusCounts = [];
                $statusDirectTotal = 0;
                foreach ($statuses as $status) {
                    foreach ($categories as $cat) {
                        if ($cat === 'Direct') {
                            $statusCounts[$status][$cat] = $records
                                ->where('status', $status)
                                ->whereNotIn('category', $indirectCategories)
                                ->count();
                        } else {
                            $statusCounts[$status][$cat] = $records
                                ->where('status', $status)
                                ->where('category', $cat)
                                ->count();
                        }
                    }
                    $statusCounts[$status]['Total'] = $records
                        ->where('status', $status)
                        ->whereIn('category', $categories)
                        ->count();
                    $statusDirectTotal += $statusCounts[$status]['Total'];
                }

                $residenceCounts = [];
                $residenceDirectTotal = 0;
                foreach ($residences as $res) {
                    foreach ($categories as $cat) {
                        if ($cat === 'Direct') {
                            $residenceCounts[$res][$cat] = $records
                                ->where('domicile', $res)
                                ->whereNotIn('category', $indirectCategories)
                                ->count();
                        } else {
                            $residenceCounts[$res][$cat] = $records
                                ->where('domicile', $res)
                                ->where('category', $cat)
                                ->count();
                        }
                    }
                    $residenceCounts[$res]['Total'] = $records
                        ->where('domicile', $res)
                        ->whereIn('category', $categories)
                        ->count();
                    $residenceDirectTotal += $residenceCounts[$res]['Total'];
                }
            @endphp

            {{-- BY DIRECT CATEGORY --}}
            <tr>
                <td rowspan="{{ count($directs) + 1 }}" class="text-center">BY DIRECT CATEGORY</td>
                @foreach ($directs as $index => $direct)
                    @if ($index > 0)
            <tr>
                @endif
                <td>{{ $direct }}</td>
                <td class="text-center">{{ $directCounts[$direct] }}</td>
                @foreach ($indirectCategories as $cat)
                    <td class="text-center"></td>
                @endforeach
                <td class="text-center"></td>
            </tr>
            @endforeach
            <tr>
                <td>TOTAL</td>
                <td class="text-center">{{ collect($directs)->sum(fn($d) => $directCounts[$d]) }}</td>
                @foreach ($indirectCategories as $cat)
                    <td class="text-center"></td>
                @endforeach
                <td class="text-center"></td>
            </tr>

            {{-- BY RESIDENCE --}}
            <tr>
                <td rowspan="{{ count($residences) + 1 }}" class="text-center">BY RESIDENCE</td>
                @foreach ($residences as $index => $res)
                    @if ($index > 0)
            <tr>
                @endif
                <td>{{ $res }}</td>
                @foreach ($categories as $cat)
                    <td class="text-center">{{ $residenceCounts[$res][$cat] }}</td>
                @endforeach
                <td class="text-center">{{ $residenceCounts[$res]['Total'] }}</td>
            </tr>
            @endforeach
            <tr>
                <td>TOTAL</td>
                @foreach ($categories as $cat)
                    <td class="text-center">
                        @php
                            $total = 0;
                            foreach ($residences as $res) {
                                $total += $residenceCounts[$res][$cat];
                            }
                        @endphp
                        {{ $total }}
                    </td>
                @endforeach
                <td class="text-center">{{ $residenceDirectTotal }}</td>
            </tr>

            {{-- BY GENDER --}}
            <tr>
                <td rowspan="{{ count($genders) + 1 }}" class="text-center">BY GENDER</td>
                @foreach ($genders as $index => $gender)
                    @if ($index > 0)
            <tr>
                @endif
                <td>{{ $gender }}</td>
                @foreach ($categories as $cat)
                    <td class="text-center">{{ $genderCounts[$gender][$cat] }}</td>
                @endforeach
                <td class="text-center">{{ $genderCounts[$gender]['Total'] }}</td>
            </tr>
            @endforeach
            <tr>
                <td>TOTAL</td>
                @foreach ($categories as $cat)
                    <td class="text-center">{{ $genderCounts['Male'][$cat] + $genderCounts['Female'][$cat] }}</td>
                @endforeach
                <td class="text-center">{{ $genderDirectTotal }}</td>
            </tr>

            {{-- BY EMPLOYMENT STATUS --}}
            <tr>
                <td rowspan="{{ count($statuses) + 1 }}" class="text-center">BY EMPLOYMENT STATUS</td>
                @foreach ($statuses as $index => $status)
                    @if ($index > 0)
            <tr>
                @endif
                <td>{{ $status }}</td>
                @foreach ($categories as $cat)
                    <td class="text-center">{{ $statusCounts[$status][$cat] }}</td>
                @endforeach
                <td class="text-center">{{ $statusCounts[$status]['Total'] }}</td>
            </tr>
            @endforeach
            <tr>
                <td>TOTAL</td>
                @foreach ($categories as $cat)
                    <td class="text-center">
                        @php
                            $total = 0;
                            foreach ($statuses as $status) {
                                $total += $statusCounts[$status][$cat];
                            }
                        @endphp
                        {{ $total }}
                    </td>
                @endforeach
                <td class="text-center">{{ $statusDirectTotal }}</td>
            </tr>

        </tbody>
    </table>

    <!-- CERTIFICATION SECTION -->
    <div>
        <div class="notes-section">
            <strong>Prepared by:</strong>
        </div>
        <table style="text-align: center">
            <tr>
                <td style="width: 25%; padding: 6px; font-size: 14px">{{ auth()->user()->name }}</td>
                <td style="width: 25%; padding: 6px; font-size: 14px">
                    {{ ucwords(str_replace('_', ' ', auth()->user()->user_type)) }}</td>
                <td style="width: 25%; padding: 6px; font-size: 14px">{{ auth()->user()->telephone }}</td>
                <td style="width: 25%; padding: 6px; font-size: 14px">{{ now()->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 2px; font-size: 12px">Authorized Representative</td>
                <td style="padding: 2px; font-size: 12px">Position / Designation</td>
                <td style="padding: 2px; font-size: 12px">Contact Nos.</td>
                <td style="padding: 2px; font-size: 12px">Date Accomplished</td>
            </tr>
        </table>

        <div class="notes-section">
            <strong>Notes:</strong>
            <ol style="font-size: 11px">
                <li>To be submitted monthly on or before the 10th day of each month</li>
                <li>Include employees employed through Manpower Services i.e., Security Guards, Janitorials,
                    Construction, Ground Maintenance, etc.</li>
                <li>For inquiries, please call (045) 499-2265</li>
            </ol>
        </div>

        <div class="certification-statement">
            <i>
                I HEREBY certify that all the information submitted electronically is true, accurate, and complete to
                the best of my knowledge and belief. I fully comprehend that any deliberate misrepresentation or
                dishonesty on my part may result in the rejection of this submission. I am fully aware of the legal
                provisions governing electronic document submission and acknowledge that any violation thereof may have
                legal consequences, including the potential refusal of this report.
            </i>
        </div>

    </div>

</body>

</html>
