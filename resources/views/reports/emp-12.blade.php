<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
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
            font-size: 12px;
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
        <img src="{{ public_path('images/logo2.png') }}" class="left-logo">
        <img src="{{ public_path('images/InvestPH.png') }}" class="right-logo">

        <p class="header-title">
            CSR AND PLACEMENT DIVISION
            <br> REPORT ON EMPLOYMENT
        </p>
    </div>

    @php
        $records = collect($records->details ?? [])
            ->filter(function ($item) {
                return $item->nationality !== 'Filipino';
            })
            ->values();

        /**
         * ✅ DB VALUE (label) => PDF CODE
         */
        $nationalityMap = [
            'American' => 'AM',
            'Australian' => 'AUS',
            'British' => 'BRIT',
            'Canadian' => 'CAN',
            'Chinese' => 'CHI',
            'Indian' => 'IND',
            'Israeli' => 'ISR',
            'Japanese' => 'JAP',
            'Korean' => 'KOR',
            'Malaysian' => 'MAL',
            'Russian' => 'RUS',
            'Singaporean' => 'SING',
            'Taiwanese' => 'TAI',
            'Ukrainian' => 'UKR',
            'Others' => 'OTHERS',
        ];

        /**
         * ✅ Reverse: CODE => LABEL (for looping)
         */
        $nationalities = array_flip($nationalityMap);

        /**
         * ✅ Normalize DB values
         */
        $records = $records->map(function ($item) use ($nationalityMap) {
            if (!isset($nationalityMap[$item->nationality])) {
                $item->nationality = 'Others';
            }
            return $item;
        });

        $directs = ['AVP and up', 'Managerial', 'Supervisory', 'Rank and File'];
        $genders = ['Male', 'Female'];
        $residences = ['Angeles', 'Mabalacat', 'Porac', 'Other Pampanga', 'Bamban', 'Capas', 'Other Tarlac', 'Others'];

        // Initialize
        foreach ($nationalities as $code => $label) {
            foreach ($directs as $d) {
                $directCounts[$code][$d] = $records->where('nationality', $label)->where('category', $d)->count();
            }

            foreach ($genders as $g) {
                $genderCounts[$code][$g] = $records->where('nationality', $label)->where('gender', $g)->count();
            }

            foreach ($residences as $r) {
                $residenceCounts[$code][$r] = $records->where('nationality', $label)->where('domicile', $r)->count();
            }

            $totals[$code] = $records->where('nationality', $label)->count();
        }
    @endphp

    <table>
        <tbody>

            <!-- HEADER -->
            <tr>
                <td rowspan="2" colspan="2">BREAKDOWN OF EMPLOYEES BY CATEGORY</td>
                <td colspan="{{ count($nationalities) }}" class="text-center">
                    EXPAT / FOREIGN EMPLOYEES
                </td>
            </tr>
            <tr>
                @foreach ($nationalities as $code => $label)
                    <td class="text-center" style="width:5%;">{{ $code }}</td>
                @endforeach
            </tr>

            <!-- ================= BY NATURE ================= -->
            <tr>
                <td rowspan="{{ count($directs) + 1 }}">BY NATURE OF WORK</td>
                <td>{{ $directs[0] }}</td>
                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $directCounts[$code][$directs[0]] }}</td>
                @endforeach
            </tr>

            @foreach (array_slice($directs, 1) as $d)
                <tr>
                    <td>{{ $d }}</td>
                    @foreach ($nationalities as $code => $label)
                        <td class="text-center">{{ $directCounts[$code][$d] }}</td>
                    @endforeach
                </tr>
            @endforeach

            <tr>
                <td>TOTAL</td>
                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $totals[$code] }}</td>
                @endforeach
            </tr>

            <!-- ================= BY RESIDENCE ================= -->
            <tr>
                <td rowspan="{{ count($residences) + 1 }}">BY RESIDENCE</td>
                <td>{{ $residences[0] }}</td>
                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $residenceCounts[$code][$residences[0]] }}</td>
                @endforeach
            </tr>

            @foreach (array_slice($residences, 1) as $r)
                <tr>
                    <td>{{ $r }}</td>
                    @foreach ($nationalities as $code => $label)
                        <td class="text-center">{{ $residenceCounts[$code][$r] }}</td>
                    @endforeach
                </tr>
            @endforeach

            <tr>
                <td>TOTAL</td>
                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $totals[$code] }}</td>
                @endforeach
            </tr>

            <!-- ================= BY GENDER ================= -->
            <tr>
                <td rowspan="{{ count($genders) + 1 }}">BY GENDER</td>
                <td>{{ $genders[0] }}</td>
                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $genderCounts[$code][$genders[0]] }}</td>
                @endforeach
            </tr>

            @foreach (array_slice($genders, 1) as $g)
                <tr>
                    <td>{{ $g }}</td>
                    @foreach ($nationalities as $code => $label)
                        <td class="text-center">{{ $genderCounts[$code][$g] }}</td>
                    @endforeach
                </tr>
            @endforeach

            <tr>
                <td>TOTAL</td>
                @foreach ($nationalities as $code => $label)
                    <td class="text-center">{{ $totals[$code] }}</td>
                @endforeach
            </tr>

        </tbody>
    </table>

    <br>

    <!-- CERTIFICATION -->
    <div>

        <div class="notes-section"><strong>Prepared by:</strong></div>

        <table style="text-align:center">
            <tr>
                <td style="width:25%">{{ auth()->user()->name }}</td>
                <td style="width:25%">{{ ucwords(str_replace('_', ' ', auth()->user()->user_type)) }}</td>
                <td style="width:25%">{{ auth()->user()->telephone }}</td>
                <td style="width:25%">{{ now()->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td>Authorized Representative</td>
                <td>Position</td>
                <td>Contact</td>
                <td>Date</td>
            </tr>
        </table>

        <div class="notes-section">
            <strong>Notes:</strong>
            <ol>
                <li>Please specify nationality under "OTHERS".</li>
            </ol>
        </div>

        <!-- LEGEND -->
        <div class="notes-section">
            <strong>Legend:</strong>
            <table>
                @foreach (array_chunk($nationalities, 4, true) as $chunk)
                    <tr>
                        @foreach ($chunk as $code => $label)
                            <td style="border:none">{{ $code }} - {{ $label }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>

        <div class="notes-section">
            <strong>Instructions:</strong>
            Submit together with employee list.
        </div>

    </div>

</body>

</html>
