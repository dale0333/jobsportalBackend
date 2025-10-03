<?php

namespace App\Http\Controllers\JobConfig;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobBenefit;
use App\Helpers\AppHelper;

class BenefitController extends Controller
{
    /**
     * Display a paginated list of Job Benefits
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = JobBenefit::query();

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
     * Store a newly created Job Benefit
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status'      => 'nullable|boolean',
        ]);

        try {
            $data = JobBenefit::create([
                'name'        => $request->name,
                'description' => $request->description,
                'status'   => $request->status ?? true,
            ]);

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Created Job Benefit '{$data->name}' (ID: {$data->id})."
            );

            return response()->json([
                'message' => 'Job benefit created successfully!',
                'data'    => $data,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create job benefit. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing Job Benefit
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status'      => 'nullable|boolean',
        ]);

        try {
            $data = JobBenefit::findOrFail($id);

            $data->update([
                'name'        => $request->name,
                'description' => $request->description,
                'status'   => $request->status ?? $data->status,
            ]);

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Updated Job Benefit '{$data->name}' (ID: {$data->id})."
            );

            return response()->json([
                'message' => 'Job benefit updated successfully!',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update job benefit. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a Job Benefit
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $data = JobBenefit::findOrFail($id);
            $name = $data->name;

            $data->delete();

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Deleted Job Benefit '{$name}' (ID: {$id})."
            );

            return response()->json([
                'message' => 'Job benefit deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete job benefit. ' . $e->getMessage(),
            ], 500);
        }
    }
}
