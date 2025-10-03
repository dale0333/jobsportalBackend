<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobVacancy;
use App\Traits\ApiResponseTrait;

class JobSeekerController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 5);
            $search  = $request->input('search');

            $query = JobVacancy::with([
                'jobService',
                'jobLocation',
                'jobType',
                'jobQualify',
                'jobLevel'
            ])->where('is_active', true);

            if ($search) {
                $query->where('title', 'like', "%{$search}%");
            }

            $data = $query->latest()->paginate($perPage);

            $formattedData = collect($data->items())->map(function ($item) {
                return [
                    'id'           => $item->id,
                    'title'        => $item->title,
                    'code'         => $item->code,
                    'content'      => $item->content,

                    'job_service'    => optional($item->jobService)->name,
                    'job_location'   => optional($item->jobLocation)->name,
                    'job_type'       => optional($item->jobType)->name,
                    'job_qualify'    => optional($item->jobQualify)->name,
                    'job_level'      => optional($item->jobLevel)->name,

                    'job_experince' => $item->job_experince,
                    'salary'       => $item->salary,
                    'deadline'     => $item->deadline,
                    'post_at'   => $item->created_at,
                ];
            });

            return response()->json([
                'data'         => $formattedData,
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch jobs', 500, $e->getMessage());
        }
    }

    public function show(string $code)
    {
        try {
            $item = JobVacancy::with([
                'jobService',
                'jobLocation',
                'jobType',
                'jobQualify',
                'jobLevel'
            ])->where('code', $code)->first();

            if (!$item) {
                return $this->errorResponse('Job not found', 404);
            }

            $data = [
                'id'           => $item->id,
                'title'        => $item->title,
                'code'         => $item->code,
                'content'      => $item->content,

                'job_service'    => optional($item->jobService)->name,
                'job_location'   => optional($item->jobLocation)->name,
                'job_type'       => optional($item->jobType)->name,
                'job_qualify'    => optional($item->jobQualify)->name,
                'job_level'      => optional($item->jobLevel)->name,

                'job_experince' => $item->job_experince,
                'salary'       => $item->salary,
                'deadline'     => $item->deadline,
                'post_at'   => $item->created_at,
            ];

            return $this->successResponse($data, 'Job retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch job', 500, $e->getMessage());
        }
    }
}
