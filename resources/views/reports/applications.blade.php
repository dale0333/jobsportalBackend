<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            font-size: 10px;
            position: relative;
        }

        .header {
            position: relative;
            text-align: center;
            margin-bottom: 15px;
        }

        .header img.left-logo {
            position: absolute;
            left: 0;
            top: 0;
            height: 45px;
            width: auto;
        }

        .header img.right-logo {
            position: absolute;
            right: 0;
            top: 0;
            height: 45px;
            width: auto;
        }

        h2 {
            margin: 0;
            font-size: 16px;
        }

        h4 {
            text-align: center;
            margin-top: 3px;
            font-weight: normal;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
            word-wrap: break-word;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }

        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }

        .status-review {
            background-color: #e0e7ff;
            color: #3730a3;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }

        .category-row {
            background-color: #e9ecef;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        .footer {
            text-align: right;
            font-size: 9px;
            margin-top: 10px;
            color: #777;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Compact styling for landscape */
        .compact td {
            padding: 4px;
        }
    </style>
</head>

<body>

    <div class="header">
        <img src="{{ public_path('images/logo2.png') }}" class="left-logo" alt="Left Logo">
        <img src="{{ public_path('images/logo1.png') }}" class="right-logo" alt="Right Logo">
        <h2>{{ $title }}</h2>
        <h4 style="margin-top: -2px">
            as of {{ $generated_at }} <br>
            Date Range: {{ $filters['dateRange'] ?? 'All Dates' }} |
            Status: {{ $filters['status'] ?? 'All' }} |
            Category: {{ $filters['category'] ?? 'All' }}
        </h4>
    </div>

    @php
        // Group applications by job category
        $groupedApplications = $applications->groupBy(function ($app) {
            return $app->jobVacancy->category->name ?? 'Uncategorized';
        });

        $statusCounts = [
            'pending' => $applications->where('status', 'pending')->count(),
            'approved' => $applications->where('status', 'approved')->count(),
            'rejected' => $applications->where('status', 'rejected')->count(),
            'under_review' => $applications->where('status', 'under_review')->count(),
        ];
    @endphp

    <!-- Summary Stats -->
    <div style="margin-bottom: 10px; padding: 8px; background: #f8f9fa; border-radius: 4px;">
        <strong>Summary:</strong>
        Total Applications: {{ $applications->count() }} |
        Pending: {{ $statusCounts['pending'] }} |
        Approved: {{ $statusCounts['approved'] }} |
        Rejected: {{ $statusCounts['rejected'] }} |
        Under Review: {{ $statusCounts['under_review'] }}
    </div>

    <table class="compact">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Applicant Name</th>
                <th style="width: 12%;">Contact</th>
                <th style="width: 15%;">Job Title</th>
                <th style="width: 12%;">Company</th>
                <th style="width: 10%;">Category</th>
                <th style="width: 8%;">Salary</th>
                <th style="width: 8%;">Applied Date</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($groupedApplications as $category => $applicationGroup)
                <tr class="category-row">
                    <td colspan="9">{{ $category }} ({{ $applicationGroup->count() }} applications)</td>
                </tr>

                @foreach ($applicationGroup as $index => $application)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $application->jobSeeker->user->name ?? 'N/A' }}</strong>
                            <br><small>{{ $application->jobSeeker->user->email ?? 'N/A' }}</small>
                        </td>
                        <td>
                            {{ $application->jobSeeker->user->telephone ?? 'N/A' }}
                            <br><small>{{ $application->jobSeeker->user->address ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $application->jobVacancy->title ?? 'N/A' }}</td>
                        <td>{{ $application->jobVacancy->employer->user->name ?? 'N/A' }}</td>
                        <td>{{ $application->jobVacancy->category->name ?? 'N/A' }}</td>
                        <td class="text-right">{{ $application->jobVacancy->salary }}</td>
                        <td class="text-center">{{ $application->created_at->format('M d, Y') }}</td>
                        <td class="text-center">
                            @php
                                $statusClass = match ($application->status) {
                                    'approved' => 'status-approved',
                                    'rejected' => 'status-rejected',
                                    'under_review' => 'status-review',
                                    default => 'status-pending',
                                };
                            @endphp
                            <span class="{{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding: 20px;">
                        No job applications found for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Report generated by {{ auth()->user()->name ?? 'System' }} | Total: {{ $applications->count() }}
            applications</p>
    </div>

</body>

</html>
