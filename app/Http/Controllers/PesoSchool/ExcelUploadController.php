<?php

namespace App\Http\Controllers\PesoSchool;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{PesoStudent, JobVacancy};
use App\Traits\ApiResponseTrait;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;

class ExcelUploadController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search');

        $query = PesoStudent::with(['user', 'category', 'jobs'])->where('user_id', $request->user()->id);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $data = $query->latest()->paginate($perPage);

        $data = ([
            'items' => $data->items(),
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
        ]);

        return $this->successResponse($data, 'Email SMTP fetch successfully', 200);
    }

    public function destroy($id)
    {
        return Excel::download(
            new StudentsExport(),
            'student_import_template.xlsx'
        );
    }

    public function show($id, Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search  = $request->input('search');
            $status  = $request->input('status');

            $student = PesoStudent::find($id);

            $query = JobVacancy::with([
                'category',
                'jobLocation',
                'jobType',
                'jobQualify',
                'jobLevel',
                'jobExperience',
                'ratings',
                'employer.user',
            ])->where('job_category', $student->type);

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
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $userId = $request->user()->id;

        $import = new StudentsImport($userId);

        Excel::import($import, $request->file('file'));

        return response()->json([
            'success' => true,
            'message' => 'Import completed',
            'data' => [
                'processed' => $import->processed,
                'success' => $import->success,
                'failed' => $import->failed,
            ],
            'errors' => $import->errors
        ]);
    }
}
