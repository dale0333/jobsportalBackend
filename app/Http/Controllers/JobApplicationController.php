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

            $user = $request->user();

            $query = JobApplication::with([
                'jobSeeker.user',
                'jobVacancy',
                'attachments',
                'jobApplicationTransactions'
            ]);

            // 👤 Job Seeker can only see their own applications
            if ($user->user_type === 'job_seeker' && $user->jobSeeker) {
                $query->where('job_seeker_id', $user->jobSeeker->id);
            }

            // 🏢 Employer sees only applications to their job vacancies
            if ($user->user_type === 'employer' && $user->employer) {
                $query->whereHas('jobVacancy', function ($sub) use ($user) {
                    $sub->where('employer_id', $user->employer->id);
                });
            }

            // 🔍 Search by applicant name, email, or job title
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('jobSeeker.user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('jobVacancy', function ($sub) use ($search) {
                        $sub->where('title', 'like', "%{$search}%");
                    });
                });
            }

            // 🎯 Filter by status
            if (!empty($status) && $status !== 'all') {
                $query->where('status', $status);
            }

            // 📄 Paginate results
            $data = $query->latest()->paginate($perPage);

            // 📊 Count applications by status
            $countQuery = JobApplication::select('status', DB::raw('COUNT(*) as count'));

            if ($user->user_type === 'job_seeker' && $user->jobSeeker) {
                $countQuery->where('job_seeker_id', $user->jobSeeker->id);
            }

            if ($user->user_type === 'employer' && $user->employer) {
                $countQuery->whereHas('jobVacancy', function ($sub) use ($user) {
                    $sub->where('employer_id', $user->employer->id);
                });
            }

            $countStatuses = $countQuery->groupBy('status')->pluck('count', 'status');

            // 📈 Build summary counts
            $counts = [
                'pending'     => $countStatuses['pending'] ?? 0,
                'withdrawn'   => $countStatuses['withdrawn'] ?? 0,
                'interview'   => $countStatuses['interview'] ?? 0,
                'rejected'    => $countStatuses['rejected'] ?? 0,
                'hired'       => $countStatuses['hired'] ?? 0,
                'all'         => $countStatuses->sum(),
            ];

            // ✅ Response payload
            $responseData = [
                'items'        => $data->items(),
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
                'count_types'  => $counts,
            ];

            return $this->successResponse($responseData, 'Job applications fetched successfully!', 200);
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
            $application = JobApplication::with([
                'jobSeeker.user',
                'jobVacancy.employer.user'
            ])->find($validated['job_application_id']);

            if (!$application) {
                return $this->errorResponse('Job application not found.', 404);
            }

            $user = $request->user();

            // ✅ Update application status
            $application->update([
                'status' => $validated['status'],
            ]);

            // ✅ Create a transaction record
            $application->jobApplicationTransactions()->create([
                'process_by' => $user->id,
                'notes'      => $validated['notes'],
                'status'     => $validated['status'],
            ]);

            // ------------------------------------------------------
            // ✅ Email Notification: Job Seeker
            // ------------------------------------------------------
            $jobSeekerUser = $application->jobSeeker->user ?? null;

            if ($jobSeekerUser) {
                AppHelper::sendNotificationEmail(
                    $jobSeekerUser,
                    'job_application_update',
                    'Your Job Application Was Updated',
                    "Your application for '{$application->jobVacancy->title}' was updated to '{$validated['status']}'.",
                    [
                        'job_vacancy'      => $application->jobVacancy->title,
                        'application_code' => $application->jobVacancy->code,
                        'status'           => $validated['status'],
                    ]
                );
            }

            // ------------------------------------------------------
            // ✅ Stored Notification: Employer (NO email)
            // ------------------------------------------------------
            $employerUser = $application->jobVacancy->employer->user ?? null;

            if ($employerUser) {
                AppHelper::storedNotification(
                    $employerUser,
                    'job_application_update',
                    'Job Application Status Updated',
                    "The application from '{$jobSeekerUser->name}' for '{$application->jobVacancy->title}' is now '{$validated['status']}'.",
                    [
                        'job_vacancy'      => $application->jobVacancy->title,
                        'application_code' => $application->jobVacancy->code,
                        'applicant_name'   => $jobSeekerUser->name,
                        'status'           => $validated['status'],
                        'cover_letter'     => $application->cover_letter ?? 'No cover letter provided',
                    ]
                );
            }

            // ✅ Activity log
            AppHelper::userLog(
                $user->id,
                "Processed Job Application '{$application->id}' — Status '{$validated['status']}'"
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
