<?php

namespace App\Http\Controllers\JobConfig;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobSkill;
use App\Helpers\AppHelper;

class SkillController extends Controller
{
    /**
     * Display a paginated list of Job Skills
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = JobSkill::query();

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
     * Store a new Job Skill
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:job_skills,name',
            'code'        => 'required|string|max:50|unique:job_skills,code',
            'description' => 'nullable|string|max:255',
            'status'      => 'nullable|boolean',
        ]);

        try {
            $data = JobSkill::create([
                'name'        => $request->name,
                'code'        => $request->code,
                'description' => $request->description,
                'status'   => $request->status ?? true,
            ]);

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Created Job Skill '{$data->name}' (Code: {$data->code}, ID: {$data->id})."
            );

            return response()->json([
                'message' => 'Job skill created successfully!',
                'data'    => $data,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create job skill. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing Job Skill
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:job_skills,name,' . $id,
            'code'        => 'required|string|max:50|unique:job_skills,code,' . $id,
            'description' => 'nullable|string|max:255',
            'status'      => 'nullable|boolean',
        ]);

        try {
            $data = JobSkill::findOrFail($id);

            $data->update([
                'name'        => $request->name,
                'code'        => $request->code,
                'description' => $request->description,
                'status'   => $request->status ?? $data->status,
            ]);

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Updated Job Skill '{$data->name}' (Code: {$data->code}, ID: {$data->id})."
            );

            return response()->json([
                'message' => 'Job skill updated successfully!',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update job skill. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a Job Skill
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $data = JobSkill::findOrFail($id);
            $name = $data->name;
            $code = $data->code;

            $data->delete();

            // Log user action
            AppHelper::userLog(
                $request->user()->id,
                "Deleted Job Skill '{$name}' (Code: {$code}, ID: {$id})."
            );

            return response()->json([
                'message' => 'Job skill deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete job skill. ' . $e->getMessage(),
            ], 500);
        }
    }
}
