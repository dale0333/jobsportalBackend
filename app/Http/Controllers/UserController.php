<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Helpers\SystemHelper;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');
        $status  = $request->input('status');
        $date    = $request->input('date');
        $type    = $request->input('type');

        $query = User::query();

        // 🔎 Search filter
        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        // 👤 User type filter
        if (!empty($type)) {
            $query->where('user_type', $type);
        }

        // 📅 Date range filter
        if (!empty($date)) {
            [$start, $end] = explode(' to ', $date);
            try {
                $startDate = \Carbon\Carbon::parse($start)->startOfDay();
                $endDate   = \Carbon\Carbon::parse($end)->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } catch (\Exception $e) {
                // Ignore invalid date format
            }
        }

        // ⚙️ Status filter
        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        // ⬇️ Paginate
        $users = $query->latest()->paginate($perPage);

        // 🧮 Count per user type (respecting current filters EXCEPT the type filter)
        $baseCountQuery = User::query();

        if (!empty($search)) {
            $baseCountQuery->where('name', 'like', "%{$search}%");
        }
        if (!empty($date)) {
            [$start, $end] = explode(' to ', $date);
            try {
                $startDate = \Carbon\Carbon::parse($start)->startOfDay();
                $endDate   = \Carbon\Carbon::parse($end)->endOfDay();
                $baseCountQuery->whereBetween('created_at', [$startDate, $endDate]);
            } catch (\Exception $e) {
            }
        }
        if (!empty($status) && $status !== 'all') {
            $baseCountQuery->where('status', $status);
        }

        $countUserTypes = $baseCountQuery
            ->select('user_type', DB::raw('COUNT(*) as count'))
            ->groupBy('user_type')
            ->pluck('count', 'user_type');

        return response()->json([
            'data'             => $users->load(['jobSeeker', 'employer']),
            'total'            => $users->total(),
            'per_page'         => $users->perPage(),
            'current_page'     => $users->currentPage(),
            'count_user_types' => [
                'job_seeker'      => $countUserTypes['job_seeker'] ?? 0,
                'employer'        => $countUserTypes['employer'] ?? 0,
                'peso_school'     => $countUserTypes['peso_school'] ?? 0,
                'manpower_agency' => $countUserTypes['manpower_agency'] ?? 0,
                'admin'           => $countUserTypes['admin'] ?? 0,
                'all'             => $countUserTypes->sum(),
            ]
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
