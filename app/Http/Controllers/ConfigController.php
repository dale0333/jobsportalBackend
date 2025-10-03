<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobConfig;
use App\Helpers\AppHelper;
use Illuminate\Support\Str;

class ConfigController extends Controller
{
    /**
     * Display a paginated list of Job Configs
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');
        $status = $request->input('status', null);

        $query = JobConfig::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status !== null) {
            $query->where('is_active', $status);
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
     * Store a newly created Job Config
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        try {
            $data = JobConfig::create([
                'name'        => $validated['name'],
                'slug'        => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'icon'        => $validated['icon'] ?? null,
                'is_active'   => $validated['is_active'] ?? true,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Created Job Config '{$data->name}', ID: {$data->id}."
            );

            return response()->json([
                'message' => 'Job config created successfully!',
                'data'    => $data,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing Job Config
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        try {
            $data = JobConfig::findOrFail($id);

            $data->update([
                'name'        => $validated['name'],
                'slug'        => Str::slug($validated['name']),
                'description' => $validated['description'] ?? $data->description,
                'icon'        => $validated['icon'] ?? $data->icon,
                'is_active'   => $validated['is_active'] ?? $data->is_active,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Updated Job Config '{$data->name}', ID: {$data->id}."
            );

            return response()->json([
                'message' => 'Job config updated successfully!',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a Job Config
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $data = JobConfig::findOrFail($id);
            $name = $data->name;

            $data->delete();

            AppHelper::userLog(
                $request->user()->id,
                "Deleted Job Config '{$name}', ID: {$id}."
            );

            return response()->json([
                'message' => 'Job config deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process. ' . $e->getMessage(),
            ], 500);
        }
    }
}
