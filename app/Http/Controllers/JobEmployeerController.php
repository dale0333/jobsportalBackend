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
            $perPage = $request->input('per_page', 10);
            $search  = $request->input('search');
            $status  = $request->input('status', null);

            $query = JobVacancy::query();

            if ($search) {
                $query->where('title', 'like', "%{$search}%");
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
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch jobs', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'         => 'required|string|max:255',
                'content'       => 'required|string',
                'job_experince' => 'required|string|max:255',
                'salary'        => 'nullable|string|max:255',
                'deadline'      => 'required',

                'job_service'  => 'required|integer|exists:job_config_details,id',
                'job_location'  => 'required|integer|exists:job_config_details,id',
                'job_type'      => 'required|integer|exists:job_config_details,id',
                'job_qualify'   => 'required|integer|exists:job_config_details,id',
                'job_level'     => 'required|integer|exists:job_config_details,id',
            ]);

            $job = JobVacancy::create([
                'user_id'       => $request->user()->id,
                'title'         => $validated['title'],
                'content'       => $validated['content'],
                'code'          => Str::upper(Str::random(8)),

                'job_service'  => $validated['job_service'],
                'job_location'  => $validated['job_location'],
                'job_type'      => $validated['job_type'],
                'job_qualify'   => $validated['job_qualify'],
                'job_level'     => $validated['job_level'],

                'job_experince' => $validated['job_experince'],
                'salary'        => $validated['salary'] ?? null,
                'deadline'      => Carbon::parse($validated['deadline'])->format('Y-m-d'),
                'is_active'     => false,
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
            $job = JobVacancy::where('code', $code)->first();

            if (!$job) {
                return $this->errorResponse('Job not found', 404);
            }

            return $this->successResponse($job, 'Job retrieved successfully');
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
                'title'         => 'required|string|max:255',
                'content'       => 'required|string',
                'job_experince' => 'required|string|max:255',
                'salary'        => 'nullable|string|max:255',
                'deadline'      => 'required',

                'job_service'  => 'required|integer|exists:job_config_details,id',
                'job_location'  => 'required|integer|exists:job_config_details,id',
                'job_type'      => 'required|integer|exists:job_config_details,id',
                'job_qualify'   => 'required|integer|exists:job_config_details,id',
                'job_level'     => 'required|integer|exists:job_config_details,id',
            ]);

            $job->update([
                'title'         => $validated['title'],
                'content'       => $validated['content'],
                'job_experince' => $validated['job_experince'],
                'salary'        => $validated['salary'] ?? null,
                'deadline'      => Carbon::parse($validated['deadline'])->format('Y-m-d'),

                'job_service'  => $validated['job_service'],
                'job_location'  => $validated['job_location'],
                'job_type'      => $validated['job_type'],
                'job_qualify'   => $validated['job_qualify'],
                'job_level'     => $validated['job_level'],
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Updated Job '{$job->title}', Code: {$job->code}."
            );

            return $this->successResponse($job, 'Job updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Something went wrong while updating the job.', 500, $e->getMessage());
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
