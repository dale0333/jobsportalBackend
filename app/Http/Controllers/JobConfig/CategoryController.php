<?php

namespace App\Http\Controllers\JobConfig;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobCategory;
use App\Helpers\AppHelper;

class CategoryController extends Controller
{
    /**
     * Display a paginated list of Job Categories
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = JobCategory::query();

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
     * Store a newly created Job Category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable',
        ]);

        try {
            $data = JobCategory::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'status' => $validated['status'] ?? 'inactive',
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Created Job Category '{$data->name}', ID: {$data->id})."
            );

            return response()->json([
                'message' => 'Job category created successfully!',
                'data'    => $data,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create job category. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing Job Category
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable',
        ]);

        try {
            $data = JobCategory::findOrFail($id);

            $data->update([
                'name' => $request->name,
                'description' => $request->description,
                'active' => $request->status ?? $data->status,
            ]);

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Updated Job Category '{$data->name}', ID: {$data->id})."
            );

            return response()->json([
                'message' => 'Job category updated successfully!',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update job category. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a Job Category
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $data = JobCategory::findOrFail($id);
            $name = $data->name;

            $data->delete();

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Deleted Job Category '{$name}', ID: {$id})."
            );

            return response()->json([
                'message' => 'Job category deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete job category. ' . $e->getMessage(),
            ], 500);
        }
    }
}
