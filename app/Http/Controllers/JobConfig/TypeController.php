<?php

namespace App\Http\Controllers\JobConfig;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobType;
use App\Helpers\AppHelper;

class TypeController extends Controller
{
    /**
     * Display a paginated list of Job Type
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = JobType::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $data = $query->latest()->paginate($perPage);

        return response()->json([
            'data'         => $data->items(),
            'total'        => $data->total(),
            'per_page'     => $data->perPage(),
            'current_page' => $data->currentPage(),
        ]);
    }

    /**
     * Store a newly created Job Type
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable',
        ]);

        try {
            $data = JobType::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'status' => $validated['status'] ?? 'inactive',
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Created Job Yype '{$data->name}', ID: {$data->id})."
            );

            return response()->json([
                'message' => 'Job type created successfully!',
                'data'    => $data,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create job type. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing Job Type
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable',
        ]);

        try {
            $data = JobType::findOrFail($id);

            $data->update([
                'name' => $request->name,
                'description' => $request->description,
                'active' => $request->status ?? $data->status,
            ]);

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Updated Job Type '{$data->name}', ID: {$data->id})."
            );

            return response()->json([
                'message' => 'Job type updated successfully!',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update job type. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a Job Type
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $data = JobType::findOrFail($id);
            $name = $data->name;

            $data->delete();

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Deleted Job Type '{$name}', ID: {$id})."
            );

            return response()->json([
                'message' => 'Job type deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete job type. ' . $e->getMessage(),
            ], 500);
        }
    }
}
