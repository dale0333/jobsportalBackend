<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{JobConfig, JobConfigDetail};
use App\Helpers\AppHelper;
use Illuminate\Support\Str;

class ConfigDetailController extends Controller
{
    /**
     * Display a paginated list of Job Configs
     */
    public function index(Request $request)
    {
        $perPage   = $request->input('per_page', 10);
        $search    = $request->input('search');
        $status    = $request->input('status', null);
        $configId  = $request->input('job_config_id');

        $configs = JobConfig::where('is_active', true)
            ->select('id', 'name', 'icon', 'slug')
            ->get();

        $query = JobConfigDetail::with('jobConfig');

        if ($configId) {
            $query->where('job_config_id', $configId);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status !== null) {
            $query->where('is_active', $status);
        }

        $data = $query->latest()->paginate($perPage);

        return response()->json([
            'configs'      => $configs,
            'items'        => $data->items(),
            'total'        => $data->total(),
            'per_page'     => $data->perPage(),
            'current_page' => $data->currentPage(),
        ]);
    }

    /**
     * Store a newly created Job Config Item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_config_id' => 'required|exists:job_configs,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
        ]);

        try {
            $data = JobConfigDetail::create([
                'job_config_id' => $validated['job_config_id'],
                'name'          => $validated['name'],
                'slug'          => Str::slug($validated['name']),
                'description'   => $validated['description'] ?? null,
                'is_active'     => $validated['is_active'] ?? true,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Created Job Config Item '{$data->name}', ID: {$data->id}."
            );

            return response()->json([
                'message' => 'Job config item created successfully!',
                'data'    => $data,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing Job Config Item
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'job_config_id' => 'required|exists:job_configs,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
        ]);

        try {
            $data = JobConfigDetail::findOrFail($id);

            $data->update([
                'job_config_id' => $validated['job_config_id'],
                'name'          => $validated['name'],
                'slug'          => Str::slug($validated['name']),
                'description'   => $validated['description'] ?? $data->description,
                'is_active'     => $validated['is_active'] ?? $data->is_active,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Updated Job Config Item '{$data->name}', ID: {$data->id}."
            );

            return response()->json([
                'message' => 'Job config item updated successfully!',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a Job Config Item
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $data = JobConfigDetail::findOrFail($id);
            $name = $data->name;

            $data->delete();

            AppHelper::userLog(
                $request->user()->id,
                "Deleted Job Config Item '{$name}', ID: {$id}."
            );

            return response()->json([
                'message' => 'Job config item deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function fetchJobTypes()
    {
        try {
            $data = JobConfig::with('jobConfigDetails')->get();
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process. ' . $e->getMessage(),
            ], 500);
        }
    }
}
