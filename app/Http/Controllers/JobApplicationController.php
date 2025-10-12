<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;
use App\Helpers\AppHelper;

class JobApplicationController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search  = $request->input('search');
            $status  = $request->input('status');
            $type  = $request->input('type');

            $query = JobApplication::with([
                'jobSeeker.user',
                'jobVacancy',
                'attachments',
                'jobApplicationTransactions'
            ]);

            if ($type === 'user') {
                $query->where('job_seeker_id',  $request->user()->jobSeeker->id);
            }

            // 🔍 Search by applicant name or job title
            if ($search) {
                $query->whereHas('jobSeeker.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhereHas('jobVacancy', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    });
            }

            // 🎯 Filter by status (pending, shortlisted, hired, etc.)
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            $data = $query->latest()->paginate($perPage);

            // 📊 Count per status
            $countStatuses = JobApplication::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            // Build response count summary
            $counts = [
                'pending'     => $countStatuses['pending'] ?? 0,
                'shortlisted' => $countStatuses['shortlisted'] ?? 0,
                'interview'   => $countStatuses['interview'] ?? 0,
                'rejected'    => $countStatuses['rejected'] ?? 0,
                'hired'       => $countStatuses['hired'] ?? 0,
                'all'         => $countStatuses->sum(),
            ];

            return response()->json([
                'data'         => $data->items(),
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
                'count_types'  => $counts,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch job applications', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_application_id' => 'required|exists:job_applications,id',
            'status'             => 'required|string',
            'notes'              => 'required|string',
        ]);

        try {
            $application = JobApplication::with('jobApplicationTransactions')->find($validated['job_application_id']);

            if (!$application) {
                return $this->errorResponse('Job application not found.', 404);
            }

            // ✅ Update main job application status
            $application->update([
                'status' => $validated['status'],
            ]);

            // ✅ Create new transaction record
            $application->jobApplicationTransactions()->create([
                'process_by' => $request->user()->id,
                'notes'      => $validated['notes'],
                'status'     => $validated['status'],
            ]);

            // ✅ Log activity
            AppHelper::userLog(
                $request->user()->id,
                "Processed Job Application '{$application->id}' - Status set to '{$validated['status']}'"
            );

            return $this->successResponse($application, 'Job application processed successfully!', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process job application.', 500, $e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $data = JobApplication::with('jobVacancy')->findOrFail($id);
            $name = $data->jobVacancy->title ?? 'Unknown';

            $data->delete();

            AppHelper::userLog(
                $request->user()->id,
                "Deleted Job Application for '{$name}', ID: {$id}."
            );

            return response()->json([
                'message' => 'Job application deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete job application. ' . $e->getMessage(),
            ], 500);
        }
    }
}
