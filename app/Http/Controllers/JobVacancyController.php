<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\{JobVacancy, JobSeeker, JobApplication};
use App\Helpers\AppHelper;
use Illuminate\Support\Str;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;

class JobVacancyController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->input('per_page', 10);
            $search  = $request->input('search');
            $status  = $request->input('status');
            $type    = $request->input('type');

            $query = JobVacancy::with([
                'category',
                'jobLocation',
                'jobType',
                'jobQualify',
                'jobLevel',
                'jobExperience',
                'ratings',
                'employer.user',
                'pesoStudents',
            ])
                ->withCount([
                    'jobApplications',
                    'jobApplications as applied_count' => fn($q) => $q->where('type', 'applied'),
                    'jobApplications as matched_count' => fn($q) => $q->where('type', 'matched'),
                    'jobApplications as invited_count' => fn($q) => $q->where('type', 'invited'),
                    'pesoStudents as peso_count',
                ]);

            if ($type !== 'admin') {
                $query->where('employer_id', $request->user()->employer->id);
            }

            if (!empty($search)) {
                $query->where('title', 'like', "%{$search}%");
            }

            if (!is_null($status)) {
                if ($status == 1) {
                    $query->where('is_active', true)
                        ->where('deadline', '>', now());
                } else if ($status == 0) {
                    $query->where(function ($q) {
                        $q->where('is_active', false)
                            ->orWhere('deadline', '<=', now());
                    });
                }
            }

            $jobs = $query->latest()->paginate($perPage);

            $jobs = ([
                'items'        => $jobs->items(),
                'total'        => $jobs->total(),
                'per_page'     => $jobs->perPage(),
                'current_page' => $jobs->currentPage(),
            ]);

            return $this->successResponse($jobs, 'Job created successfully!', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch jobs' . $e->getMessage(), 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'            => 'required|string|max:255',
                'qualifications'   => 'required|string',
                'description'      => 'required|string',
                'salary'           => 'nullable|string|max:255',
                'deadline'         => 'required',

                'job_category'     => 'required|integer|exists:categories,id',
                'available'        => 'required|integer',
                'job_sub_category' => 'required|array',
                'job_sub_category.*' => 'integer|exists:sub_categories,id',

                'job_location'     => 'required|integer|exists:sub_attributes,id',
                'job_type'         => 'required|integer|exists:sub_attributes,id',
                'job_qualify'      => 'required|integer|exists:sub_attributes,id',
                'job_level'        => 'required|integer|exists:sub_attributes,id',
                'job_experience'   => 'required|integer|exists:sub_attributes,id',
                'is_active'        => 'nullable|boolean',
            ]);

            $job = JobVacancy::create([
                'employer_id'   => $request->user()->employer->id,
                'title'         => $validated['title'],
                'qualifications' => $validated['qualifications'],
                'description'   => $validated['description'],
                'code'          => Str::upper(Str::random(10)),

                'job_category'    => $validated['job_category'],
                'job_sub_category' => $validated['job_sub_category'],
                'job_location'  => $validated['job_location'],
                'job_type'      => $validated['job_type'],
                'job_qualify'   => $validated['job_qualify'],
                'job_level'     => $validated['job_level'],

                'job_experience' => $validated['job_experience'],
                'available'     => $validated['available'],
                'salary'        => $validated['salary'] ?? null,
                'deadline'      => Carbon::parse($validated['deadline'])->format('Y-m-d'),
                'is_active'     => $validated['is_active'] ?? false,
            ]);

            $jobSubCategories = $validated['job_sub_category'];

            $jobSeekers = JobSeeker::all();

            foreach ($jobSeekers as $js) {

                // Ensure services is an array (decode JSON)
                $services = is_array($js->services)
                    ? $js->services
                    : json_decode($js->services, true);

                if (!$services) {
                    continue;
                }

                // Intersection logic
                $match = array_intersect($jobSubCategories, $services);

                if (!empty($match)) {

                    // Check if application exists already
                    $exists = JobApplication::where('job_seeker_id', $js->id)
                        ->where('job_vacancy_id', $job->id)
                        ->exists();

                    if (!$exists) {
                        JobApplication::create([
                            'job_seeker_id'   => $js->id,
                            'job_vacancy_id'  => $job->id,
                            'type'            => 'matched',
                            'status'          => 0,
                        ]);
                    }
                }
            }

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

    public function update(Request $request, string $code)
    {
        try {
            $job = JobVacancy::where('code', $code)->first();

            if (!$job) {
                return $this->errorResponse('Job not found', 404);
            }

            $validated = $request->validate([
                'title'            => 'required|string|max:255',
                'qualifications'   => 'required|string',
                'description'      => 'required|string',
                'salary'           => 'nullable|string|max:255',
                'deadline'         => 'required',

                'job_category'     => 'required|integer|exists:categories,id',
                'available'        => 'required|integer',
                'job_sub_category' => 'required|array',
                'job_sub_category.*' => 'integer|exists:sub_categories,id',

                'job_location'     => 'required|integer|exists:sub_attributes,id',
                'job_type'         => 'required|integer|exists:sub_attributes,id',
                'job_qualify'      => 'required|integer|exists:sub_attributes,id',
                'job_level'        => 'required|integer|exists:sub_attributes,id',
                'job_experience'   => 'required|integer|exists:sub_attributes,id',
                'is_active'        => 'nullable|boolean',
            ]);

            $job->update([
                'title'           => $validated['title'],
                'qualifications'  => $validated['qualifications'],
                'description'     => $validated['description'],
                'available'       => $validated['available'],
                'job_experience'  => $validated['job_experience'],
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

            return $this->successResponse($job, 'Job updated successfully', 201);
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

    public function show(string $code)
    {
        try {
            $data = JobVacancy::with([
                'category',
                'jobLocation',
                'jobType',
                'jobQualify',
                'jobLevel',
                'jobExperience',
                'ratings',
                'employer.user',
                'pesoStudents',
            ])
                ->withCount([
                    'jobApplications',
                    'jobApplications as applied_count' => fn($q) => $q->where('type', 'applied'),
                    'jobApplications as matched_count' => fn($q) => $q->where('type', 'matched'),
                    'jobApplications as invited_count' => fn($q) => $q->where('type', 'invited'),
                    'pesoStudents as peso_count',
                ])
                ->where('code', $code)
                ->first();

            if (!$data) {
                return $this->errorResponse('Job not found', 404);
            }

            // compute average rating (1–5)
            $data->average_rating = round($data->ratings()->avg('rate') ?? 0, 1);

            return $this->successResponse($data, 'Job retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch job', 500, $e->getMessage());
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
