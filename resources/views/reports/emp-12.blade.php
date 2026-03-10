<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
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
            margin-bottom: 25px;
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
            margin-top: 15px;
            font-size: 11px;
        }

        .notes-section ol {
            margin-top: 5px;
            margin-bottom: 20px;
        }

        .notes-section li {
            margin-bottom: 5px;
        }

        .certification-statement {
            margin-top: 20px;
            font-weight: bold;
            font-size: 12px;
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

    <table>
        <tbody>
            <tr>
                <td rowspan="2" colspan="2">BREAKDOWN OF EMPLOYEES BY CATEGORY</td>
                <td colspan="14" class="text-center">EXPAT / FOREIGN EMPLOYEES</td>
            </tr>

            @php
                $records = $records->details;

                // Nationalities based on your labels
                $nationalities = [
                    'AM' => 'American',
                    'AUS' => 'Australian',
                    'CAN' => 'Canadian',
                    'BRIT' => 'British',
                    'IND' => 'Indian',
                    'ISR' => 'Israeli',
                    'JAP' => 'Japanese',
                    'KOR' => 'Korean',
                    'MAL' => 'Malaysian',
                    'RUS' => 'Russian',
                    'SING' => 'Singaporean',
                    'TAI' => 'Taiwanese',
                    'UKR' => 'Ukrainian',
                    'OTHERS' => 'Others',
                ];

                // Create reverse lookup (label → code)
                $nationalityLabels = array_flip($nationalities);

                // Normalize nationalities (if not found, assign "Others")
                $normalizedRecords = $records->map(function ($item) use ($nationalityLabels) {
                    if (!isset($nationalityLabels[$item->nationality])) {
                        $item->nationality = 'Others';
                    }
                    return $item;
                });

                // Direct categories
                $directs = ['AVP and up', 'Managerial', 'Supervisory', 'Rank and File'];

                // Genders
                $genders = ['Male', 'Female'];

                // Residences
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

                // Initialize counts arrays
                $directCountsByNationality = [];
                $genderCountsByNationality = [];
                $residenceCountsByNationality = [];
                $totalByNationality = [];

                foreach ($nationalities as $code => $label) {
                    // --- BY NATURE OF WORK ---
                    foreach ($directs as $direct) {
                        $directCountsByNationality[$code][$direct] = $normalizedRecords
                            ->where('nationality', $label)
                            ->where('category', $direct)
                            ->count();
                    }

                    // --- BY GENDER ---
                    foreach ($genders as $gender) {
                        $genderCountsByNationality[$code][$gender] = $normalizedRecords
                            ->where('nationality', $label)
                            ->where('gender', $gender)
                            ->count();
                    }

                    // --- BY RESIDENCE ---
                    foreach ($residences as $residence) {
                        $residenceCountsByNationality[$code][$residence] = $normalizedRecords
                            ->where('nationality', $label)
                            ->where('domicile', $residence)
                            ->count();
                    }

                    // --- TOTAL BY NATIONALITY ---
                    $totalByNationality[$code] = $normalizedRecords->where('nationality', $label)->count();
                }
            @endphp

            <tr>
                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $code }}</td>
                @endforeach
            </tr>

            {{-- ================== BY NATURE OF WORK ================== --}}
            <tr>
                <td rowspan="{{ count($directs) + 1 }}" class="text-center">BY NATURE OF WORK</td>

                @foreach ($directs as $index => $direct)
                    @if ($index > 0)
            <tr>
                @endif
                <td>{{ $direct }}</td>

                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $directCountsByNationality[$code][$direct] }}</td>
                @endforeach
            </tr>
            @endforeach

            <tr>
                <td>TOTAL</td>
                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $totalByNationality[$code] }}</td>
                @endforeach
            </tr>

            {{-- ================== BY RESIDENCE ================== --}}
            <tr>
                <td rowspan="{{ count($residences) + 1 }}" class="text-center">BY RESIDENCE</td>

                @foreach ($residences as $index => $residence)
                    @if ($index > 0)
            <tr>
                @endif
                <td>{{ $residence }}</td>

                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $residenceCountsByNationality[$code][$residence] }}</td>
                @endforeach
            </tr>
            @endforeach

            <tr>
                <td>TOTAL</td>
                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $totalByNationality[$code] }}</td>
                @endforeach
            </tr>

            {{-- ================== BY GENDER ================== --}}
            <tr>
                <td rowspan="{{ count($genders) + 1 }}" class="text-center">BY GENDER</td>

                @foreach ($genders as $index => $gender)
                    @if ($index > 0)
            <tr>
                @endif
                <td>{{ $gender }}</td>

                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $genderCountsByNationality[$code][$gender] }}</td>
                @endforeach
            </tr>
            @endforeach

            <tr>
                <td>TOTAL</td>
                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $totalByNationality[$code] }}</td>
                @endforeach
            </tr>

        </tbody>
    </table>

    <br>
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
            <ol>
                <li>Please specify the nationality for the "OTHERS" column.</li>
            </ol>
        </div>

        <div class="notes-section">
            <strong>Legend:</strong>
            <table>
                @php
                    // Nationalities list
                    $nationalities = [
                        'AM' => 'American',
                        'AUS' => 'Australian',
                        'CAN' => 'Canadian',
                        'BRIT' => 'British',
                        'IND' => 'Indian',
                        'ISR' => 'Israeli',
                        'JAP' => 'Japanese',
                        'KOR' => 'Korean',
                        'MAL' => 'Malaysian',
                        'RUS' => 'Russian',
                        'SING' => 'Singaporean',
                        'TAI' => 'Taiwanese',
                        'UKR' => 'Ukrainian',
                        'OTHERS' => 'Others',
                    ];

                    $chunks = array_chunk($nationalities, 4, true);
                @endphp

                @foreach ($chunks as $chunk)
                    <tr>
                        @foreach ($chunk as $code => $label)
                            <td style="padding: 2px 8px; font-size: 12px; border: none">{{ $code }} -
                                {{ $label }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>

        <div class="notes-section">
            <strong>Instructions:</strong>
            This report should be submitted together with the attached detailed list of employees.
        </div>

    </div>

</body>

</html>
