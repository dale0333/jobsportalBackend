<?php

namespace App\Http\Controllers\JobConfig;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobLocation;
use App\Helpers\AppHelper;

class LocationController extends Controller
{
    /**
     * Display a paginated list of Job Locations
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = JobLocation::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $data = $query->latest()->paginate($perPage);

        return response()->json([
            'data'         => $data,
            'total'        => $data->total(),
            'per_page'     => $data->perPage(),
            'current_page' => $data->currentPage(),
        ]);
    }

    /**
     * Store a newly created Job Location
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:job_locations,code',
            'address'     => 'nullable|string|max:255',
            'status'      => 'nullable|boolean',
        ]);

        try {
            $data = JobLocation::create([
                'name'      => $request->name,
                'code'      => $request->code,
                'address'   => $request->address,
                'status' => $request->status ?? true,
            ]);

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Created Job Location '{$data->name}' (Code: {$data->code}, ID: {$data->id})."
            );

            return response()->json([
                'message' => 'Job location created successfully!',
                'data'    => $data,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create job location. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing Job Location
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:job_locations,code,' . $id,
            'address'     => 'nullable|string|max:255',
            'status'      => 'nullable|boolean',
        ]);

        try {
            $data = JobLocation::findOrFail($id);

            $data->update([
                'name'      => $request->name,
                'code'      => $request->code,
                'address'   => $request->address,
                'status' => $request->status ?? $data->status,
            ]);

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Updated Job Location '{$data->name}' (Code: {$data->code}, ID: {$data->id})."
            );

            return response()->json([
                'message' => 'Job location updated successfully!',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update job location. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a Job Location
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $data = JobLocation::findOrFail($id);
            $name = $data->name;
            $code = $data->code;

            $data->delete();

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Deleted Job Location '{$name}' (Code: {$code}, ID: {$id})."
            );

            return response()->json([
                'message' => 'Job location deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete job location. ' . $e->getMessage(),
            ], 500);
        }
    }
}
