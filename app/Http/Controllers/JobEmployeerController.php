<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\JobVacancy;
use App\Helpers\AppHelper;
use Illuminate\Support\Str;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;

class JobEmployeerController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->input('per_page', 10);
            $search  = $request->input('search');
            $status  = $request->input('status');

            $query = JobVacancy::with(['employer'])
                ->where('employer_id', $request->user()->employer->id);

            if ($search) {
                $query->where('title', 'like', "%{$search}%");
            }

            if (!is_null($status)) {
                $query->where('is_active', $status);
            }

            $jobs = $query->latest()->paginate($perPage);

            return response()->json([
                'success'      => true,
                'data'         => $jobs->items(),
                'total'        => $jobs->total(),
                'per_page'     => $jobs->perPage(),
                'current_page' => $jobs->currentPage(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch jobs', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'            => 'required|string|max:255',
                'content'          => 'required|string',
                'job_experience'   => 'required|string|max:255',
                'salary'           => 'nullable|string|max:255',
                'deadline'         => 'required',

                'job_category'     => 'required|integer|exists:categories,id',
                'job_sub_category' => 'required|array',
                'job_sub_category.*' => 'integer|exists:sub_categories,id',

                'job_location'     => 'required|integer|exists:job_config_details,id',
                'job_type'         => 'required|integer|exists:job_config_details,id',
                'job_qualify'      => 'required|integer|exists:job_config_details,id',
                'job_level'        => 'required|integer|exists:job_config_details,id',
                'is_active'        => 'nullable|boolean',
            ]);

            $job = JobVacancy::create([
                'employer_id'   => $request->user()->id,
                'title'         => $validated['title'],
                'content'       => $validated['content'],
                'code'          => (string) Str::uuid(),

                'job_category'    => $validated['job_category'],
                'job_sub_category' => $validated['job_sub_category'],
                'job_location'  => $validated['job_location'],
                'job_type'      => $validated['job_type'],
                'job_qualify'   => $validated['job_qualify'],
                'job_level'     => $validated['job_level'],

                'job_experience' => $validated['job_experience'],
                'salary'        => $validated['salary'] ?? null,
                'deadline'      => Carbon::parse($validated['deadline'])->format('Y-m-d'),
                'is_active'     => $validated['is_active'] ?? false,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Created Job '{$job->title}', Code: {$job->code}."
            );

            return $this->successResponse($job, 'Job created successfully!', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Something went wrong while creating the job.', 500, $e->getMessage());
        }
    }

    public function show(string $code)
    {
        try {
            $item = JobVacancy::where('code', $code)->first();

            if (!$item) {
                return $this->errorResponse('Job not found', 404);
            }

            $data = [
                'title'        => $item->title,
                'content'      => $item->content,
                'job_category'    => $item->job_category,
                'job_sub_category' => $item->job_sub_category,

                'job_location'   => $item->job_location,
                'job_type'       => $item->job_type,
                'job_qualify'    => $item->job_qualify,
                'job_level'      => $item->job_level,

                'job_experience' => $item->job_experience,
                'salary'       => $item->salary,
                'deadline'     => $item->deadline,
                'is_active'     => $item->is_active,
            ];

            return response()->json([
                'data' => $data,
                'status' => true,
                'message'  => "Job retrieved successfully",
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch job', 500, $e->getMessage());
        }
    }

    public function update(Request $request, string $code)
    {
        try {
            $job = JobVacancy::where('code', $code)->first();

            if (!$job) {
                return $this->errorResponse('Job not found', 404);
            }

            $validated = $request->validate([
                'title'            => 'required|string|max:255',
                'content'          => 'required|string',
                'job_experience'   => 'required|string|max:255',
                'salary'           => 'nullable|string|max:255',
                'deadline'         => 'required',

                'job_category'     => 'required|integer|exists:categories,id',
                'job_sub_category' => 'required|array',
                'job_sub_category.*' => 'integer|exists:sub_categories,id',

                'job_location'     => 'required|integer|exists:job_config_details,id',
                'job_type'         => 'required|integer|exists:job_config_details,id',
                'job_qualify'      => 'required|integer|exists:job_config_details,id',
                'job_level'        => 'required|integer|exists:job_config_details,id',
                'is_active'        => 'nullable|boolean',
            ]);

            $job->update([
                'title'           => $validated['title'],
                'content'         => $validated['content'],
                'job_experience'   => $validated['job_experience'],
                'salary'          => $validated['salary'] ?? null,
                'deadline'        => Carbon::parse($validated['deadline'])->format('Y-m-d'),

                'job_category'    => $validated['job_category'],
                'job_sub_category' => $validated['job_sub_category'],
                'job_location'    => $validated['job_location'],
                'job_type'        => $validated['job_type'],
                'job_qualify'     => $validated['job_qualify'],
                'job_level'       => $validated['job_level'],
                'is_active'       => $validated['is_active'] ?? $job->is_active,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Updated Job '{$job->title}', Code: {$job->code}."
            );

            return $this->successResponse($job, 'Job updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Something went wrong while updating the job.',
                500,
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $job = JobVacancy::find($id);

            if (!$job) {
                return $this->errorResponse('Job not found', 404);
            }

            AppHelper::userLog(
                $request->user()->id,
                "Deleted Job '{$job->title}', Code: {$job->code}."
            );

            $job->delete();

            return $this->successResponse(null, 'Job deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete job', 500, $e->getMessage());
        }
    }
}
