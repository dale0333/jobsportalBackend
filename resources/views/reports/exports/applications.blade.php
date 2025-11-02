<table>
    <thead>
        <tr>
            <th colspan="10" style="text-align: center; font-size: 16px; font-weight: bold;">{{ $title }}</th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center;">
                Generated on: {{ $generated_at }}<br>
                Date Range: {{ $filters['dateRange'] ?? 'All Dates' }} |
                Status: {{ $filters['status'] ?? 'All' }} |
                Category: {{ $filters['category'] ?? 'All' }}
            </th>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 5;">#</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 20;">Applicant Name</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 15;">Contact</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 18;">Job Title</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 15;">Company</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 12;">Category</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 12;">Salary</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 12;">Applied Date</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 10;">Status</th>
        </tr>
    </thead>

    <tbody>
        @php
            // Group applications by job category (same as PDF)
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

        <!-- Summary Row -->
        <tr>
            <td colspan="9"
                style="background-color: #f8f9fa; font-weight: bold; border: 1px solid #ccc; padding: 8px;">
                Summary: Total Applications: {{ $applications->count() }} |
                Pending: {{ $statusCounts['pending'] }} |
                Approved: {{ $statusCounts['approved'] }} |
                Rejected: {{ $statusCounts['rejected'] }} |
                Under Review: {{ $statusCounts['under_review'] }}
            </td>
        </tr>

        @forelse ($groupedApplications as $category => $applicationGroup)
            <tr>
                <td colspan="9"
                    style="background-color: #e9ecef; font-weight: bold; text-transform: uppercase; text-align: center; border: 1px solid #ccc;">
                    {{ $category }} ({{ $applicationGroup->count() }} applications)
                </td>
            </tr>

            @foreach ($applicationGroup as $index => $application)
                <tr>
                    <td style="border: 1px solid #ccc; text-align: center;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">
                        <strong>{{ $application->jobSeeker->user->name ?? 'N/A' }}</strong>
                        <br><small style="color: #666;">{{ $application->jobSeeker->user->email ?? 'N/A' }}</small>
                    </td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">
                        {{ $application->jobSeeker->user->telephone ?? 'N/A' }}
                        <br><small style="color: #666;">{{ $application->jobSeeker->user->address ?? 'N/A' }}</small>
                    </td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">
                        {{ $application->jobVacancy->title ?? 'N/A' }}</td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">
                        {{ $application->jobVacancy->employer->user->name ?? 'N/A' }}</td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">
                        {{ $application->jobVacancy->category->name ?? 'N/A' }}</td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word; text-align: right;">
                        ₱{{ number_format($application->jobVacancy->salary_range_from ?? 0, 0) }} -
                        ₱{{ number_format($application->jobVacancy->salary_range_to ?? 0, 0) }}
                    </td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word; text-align: center;">
                        {{ $application->created_at->format('M d, Y') }}</td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word; text-align: center;">
                        @php
                            $statusColor = match ($application->status) {
                                'approved' => '#d1fae5',
                                'rejected' => '#fee2e2',
                                'under_review' => '#e0e7ff',
                                default => '#fef3c7',
                            };
                        @endphp
                        <span
                            style="background-color: {{ $statusColor }}; padding: 2px 6px; border-radius: 3px; font-weight: bold;">
                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="9" style="text-align: center; border: 1px solid #ccc; padding: 20px;">
                    No job applications found for the selected filters.
                </td>
            </tr>
        @endforelse

        <!-- Total Count Footer -->
        @if ($applications->count() > 0)
            <tr>
                <td colspan="9"
                    style="background-color: #f1f5f9; font-weight: bold; text-align: center; border: 1px solid #ccc;">
                    Total Applications: {{ $applications->count() }}
                </td>
            </tr>
        @endif
    </tbody>
</table>
