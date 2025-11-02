<table>
    <thead>
        <tr>
            <th colspan="6" style="text-align: center; font-size: 16px; font-weight: bold;">{{ $title }}</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center;">
                Generated on: {{ $generated_at }}<br>
                Date Range: {{ $filters['dateRange'] ?? 'All Dates' }}
            </th>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 25;">Company Name</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 25;">Position</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 20;">Qualification</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 20;">Work Experience</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 20;">Salary</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 15;">Vacants</th>
        </tr>
    </thead>

    <tbody>
        @php
            // ✅ Group vacancies by category and sort alphabetically (same as PDF)
            $groupedVacancies = $vacancies
                ->sortBy(fn($job) => $job->category->name ?? 'Unknown')
                ->groupBy(fn($job) => $job->category->name ?? 'Uncategorized');

            // ✅ Compute grand total of available vacancies
            $grandTotal = $vacancies->sum('available');
        @endphp

        @forelse ($groupedVacancies as $categoryName => $jobs)
            <tr>
                <td colspan="6"
                    style="background-color: #e9ecef; font-weight: bold; text-transform: uppercase; text-align: center; border: 1px solid #ccc;">
                    {{ $categoryName }}
                </td>
            </tr>

            @foreach ($jobs as $job)
                <tr>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">{{ $job->employer->user->name ?? 'N/A' }}
                    </td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">{{ $job->title }}</td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">{{ $job->jobQualify->name ?? 'N/A' }}
                    </td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">
                        {{ $job->job_experience ? $job->job_experience . ' year(s)' : 'N/A' }}</td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">{{ $job->salary ?? 'N/A' }}</td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word; text-align: center;">
                        {{ $job->available ?? 0 }}</td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="6" style="text-align: center; border: 1px solid #ccc;">No job vacancies found.</td>
            </tr>
        @endforelse

        @if ($vacancies->count() > 0)
            <tr>
                <td colspan="5"
                    style="background-color: #f1f3f5; font-weight: bold; text-align: center; border: 1px solid #ccc;">
                    Overall Total Vacancies
                </td>
                <td style="background-color: #f1f3f5; font-weight: bold; text-align: center; border: 1px solid #ccc;">
                    {{ $grandTotal }}
                </td>
            </tr>
        @endif
    </tbody>
</table>
