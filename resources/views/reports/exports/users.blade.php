<table>
    <thead>
        <tr>
            <th colspan="4" style="text-align: center; font-size: 16px; font-weight: bold;">{{ $title }}</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center;">
                Generated on: {{ $generated_at }}<br>
                Date Range: {{ $filters['dateRange'] ?? 'All Dates' }}
            </th>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 30;">Name</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 30;">Email</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 50;">Address</th>
            <th style="background-color: #f8f9fa; border: 1px solid #ccc; width: 20;">Contact #</th>
        </tr>
    </thead>

    <tbody>
        @php
            // ✅ Group users by role (same as PDF)
            $groupedUsers = $users->groupBy(fn($user) => ucfirst($user->user_type ?? 'Unspecified'));
        @endphp

        @forelse ($groupedUsers as $role => $userGroup)
            <tr>
                <td colspan="4"
                    style="background-color: #e9ecef; font-weight: bold; text-transform: uppercase; text-align: center; border: 1px solid #ccc;">
                    {{ str_replace('_', ' ', $role) }}
                </td>
            </tr>

            @foreach ($userGroup as $user)
                <tr>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">{{ $user->name }}</td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">{{ $user->email }}</td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">{{ $user->address ?? 'N/A' }}</td>
                    <td style="border: 1px solid #ccc; word-wrap: break-word;">{{ $user->telephone ?? 'N/A' }}</td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="4" style="text-align: center; border: 1px solid #ccc;">No users found for the selected
                    filters.</td>
            </tr>
        @endforelse
    </tbody>
</table>
