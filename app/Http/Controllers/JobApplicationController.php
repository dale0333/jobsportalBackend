<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Models\{JobApplication, JobVacancy};
use Illuminate\Support\Facades\DB;
use App\Helpers\AppHelper;

use App\Jobs\SendApplicationStatusNotification;

class JobApplicationController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search  = $request->input('search');
            $status  = $request->input('status');
            $jobCode = $request->input('job_code');
            $type    = $request->input('type');
            $user    = $request->user();

            /**
             * --------------------------------------
             *  MAIN QUERY (FOR TABLE ITEMS)
             * --------------------------------------
             */
            $query = JobApplication::with([
                'jobSeeker.user',
                'jobVacancy',
                'attachments',
                'jobApplicationTransactions'
            ]);

            // Filter: Job Code
            if (!empty($jobCode)) {
                $query->whereHas('jobVacancy', function ($sub) use ($jobCode) {
                    $sub->where('code', $jobCode);
                });
                $query->where('type', $type);
            }

            // Filter: by Job Seeker
            if ($user->user_type === 'job_seeker' && $user->jobSeeker) {
                $query->where('job_seeker_id', $user->jobSeeker->id);
            }

            // Filter: by Employer
            if ($user->user_type === 'employer' && $user->employer) {
                $query->whereHas('jobVacancy', function ($sub) use ($user) {
                    $sub->where('employer_id', $user->employer->id);
                });
            }

            // Search Filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    // Search in job seeker → user (name or email)
                    $q->whereHas('jobSeeker.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })

                        // OR search in job seeker profile (skills or education)
                        ->orWhereHas('jobSeeker', function ($jsQuery) use ($search) {
                            $jsQuery->where('skills', 'like', "%{$search}%")
                                ->orWhere('field_of_study', 'like', "%{$search}%")
                                ->orWhere('education_level', 'like', "%{$search}%")
                                ->orWhere('preferred_location', 'like', "%{$search}%");
                        });
                });
            }

            // Status Filter
            if (!empty($status) && $status !== 'all') {
                $query->where('status', $status);
            }

            // Pagination
            $data = $query->latest()->paginate($perPage);

            /**
             * --------------------------------------
             *  COUNT QUERY (MATCHES MAIN FILTERS)
             * --------------------------------------
             */
            $countQuery = JobApplication::query();

            // Same filters as main query
            if (!empty($jobCode)) {
                $countQuery->whereHas('jobVacancy', function ($sub) use ($jobCode) {
                    $sub->where('code', $jobCode);
                });
            }

            if ($user->user_type === 'job_seeker' && $user->jobSeeker) {
                $countQuery->where('job_seeker_id', $user->jobSeeker->id);
            }

            if ($user->user_type === 'employer' && $user->employer) {
                $countQuery->whereHas('jobVacancy', function ($sub) use ($user) {
                    $sub->where('employer_id', $user->employer->id);
                });
            }

            if (!empty($search)) {
                $countQuery->where(function ($q) use ($search) {
                    $q->whereHas('jobSeeker.user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                        ->orWhereHas('jobVacancy', function ($sub) use ($search) {
                            $sub->where('title', 'like', "%{$search}%");
                        });
                });
            }

            if (!empty($status) && $status !== 'all') {
                $countQuery->where('status', $status);
            }

            /**
             * EXCLUDE "matched" and "invited" types from status counts
             * Only count applications where type is NOT 'matched' and NOT 'invited'
             */
            $countQuery->whereNotIn('type', ['matched', 'invited']);

            // Group & Count
            $countStatuses = $countQuery
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            /**
             * Build summary counts
             */
            $counts = [
                'pending'     => $countStatuses['pending'] ?? 0,
                'withdrawn'   => $countStatuses['withdrawn'] ?? 0,
                'interview'   => $countStatuses['interview'] ?? 0,
                'rejected'    => $countStatuses['rejected'] ?? 0,
                'hired'       => $countStatuses['hired'] ?? 0,
                'all'         => $countStatuses->sum(),
            ];

            /**
             * Final response
             */
            return $this->successResponse([
                'items'        => $data->items(),
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
                'count_types'  => $counts,
            ], 'Job applications fetched successfully!');
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
                'type'   => 'applied',
            ]);

            // ✅ Create a transaction record
            $application->jobApplicationTransactions()->create([
                'process_by' => $user->id,
                'notes'      => $validated['notes'],
                'status'     => $validated['status'],
            ]);

            $this->storeNotification($application, $application->jobSeeker->user ?? null, $validated['status'], $validated['notes']);

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

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'applicant_ids' => 'required|array',
            'applicant_ids.*' => 'integer',
            'status' => 'required|string|in:invited,withdrawn,pending,interview,rejected,hired',
        ]);

        try {
            // Find the job vacancy
            $job = JobVacancy::where('code', $id)->firstOrFail();

            // Determine the update data based on status
            $action = [];
            if ($validated['status'] === 'invited') {
                $action = [
                    'type' => 'invited',
                    'log' => 'invited',
                    'success' => 'Invitations sent successfully to %d applicant(s)!'
                ];
            } else if ($validated['status'] === 'withdrawn') {
                $action = [
                    'type' => 'matched',
                    'log' => 'withdrawn invitations for',
                    'success' => 'Invitations withdrawn successfully from %d applicant(s)!'
                ];
            } else {
                $action = [
                    'type' => 'applied',
                    'log' => 'updated',
                    'success' => 'Applications updated successfully for %d applicant(s)!'
                ];
            }

            // Get applications before update for notification
            $applications = JobApplication::with(['jobSeeker.user'])
                ->where('job_vacancy_id', $job->id)
                ->whereIn('id', $validated['applicant_ids'])
                ->get();

            // Update the applications
            $updatedCount = JobApplication::where('job_vacancy_id', $job->id)
                ->whereIn('id', $validated['applicant_ids'])
                ->update([
                    'type' => $action['type'],
                    'updated_at' => now(),
                ]);

            $this->storeNotificationInvite(
                $applications,
                $job,
                $validated['status'],
                $action,
                $request,
                $updatedCount
            );

            // Log the action
            AppHelper::userLog(
                $request->user()->id,
                "{$action['log']} job '{$job->title}', Code: {$job->code}. Affected applications: {$updatedCount}"
            );

            $successMessage = sprintf($action['success'], $updatedCount);

            return $this->successResponse($successMessage, 'Process successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update job applications: ' . $e->getMessage(), 500);
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

    // privite ==================================================================
    private function storeNotificationInvite($applications, $job, $status, $action, $request, $updatedCount)
    {
        foreach ($applications as $application) {
            $title = "Application has been " . ucfirst($status);
            $message = 'Your Application has been ' . ucfirst($status) . ' invitations for ' . $job->title;

            // Email Notification
            SendApplicationStatusNotification::dispatch(
                $application->jobSeeker->user,
                'application_type_update',
                $title,
                $message,
                [
                    'job_title' => $job->title,
                    'job_code' => $job->code,
                    'status' => $status,
                    'employer_name' => $job->employer->user->name ?? 'Employer',
                    'updated_at' => now()->toDateTimeString(),
                    'application_id' => $application->id,
                    'action_type' => $status,
                ]
            );

            // System Notification to Job Seeker
            AppHelper::systemNotificaiton(
                $application->jobSeeker->user,
                'application_type_update',
                $title,
                $message,
                [
                    'job_title' => $job->title,
                    'job_code' => $job->code,
                    'status' => $status,
                    'employer_name' => $job->employer->user->name ?? 'Employer',
                    'updated_at' => now()->toDateTimeString(),
                ]
            );
        }

        // System Notification for Employer
        AppHelper::systemNotificaiton(
            $request->user(),
            'application_action',
            'Application Action Completed',
            "You have {$action['log']} {$updatedCount} application(s) for '{$job->title}'",
            [
                'job_title' => $job->title,
                'job_code' => $job->code,
                'action' => $status,
                'affected_applications' => $updatedCount,
                'action_type' => $action['log'],
            ]
        );
    }

    private function storeNotification($application, $jobSeekerUser, $status, $notes)
    {
        // -----------------------------------------
        // Employer stored notification
        // -----------------------------------------
        $employerUser = $application->jobVacancy->employer->user ?? null;

        if ($employerUser && $jobSeekerUser) {
            AppHelper::storedNotification(
                $employerUser,
                'job_application_update',
                'Job Application Status Updated',
                "The application from '{$jobSeekerUser->name}' for '{$application->jobVacancy->title}' is now '{$status}'.",
                [
                    'job_vacancy'      => $application->jobVacancy->title,
                    'application_code' => $application->jobVacancy->code,
                    'applicant_name'   => $jobSeekerUser->name,
                    'status'           => $status,
                    'type'             => "Applied",
                    'cover_letter'     => $application->cover_letter ?? 'No cover letter provided',
                ]
            );
        }

        // -----------------------------------------
        // Job Seeker email & system notification
        // -----------------------------------------
        if ($jobSeekerUser) {
            $title = "Application Status Updated";
            $message = $notes;
            $data = [
                'job_title'      => $application->jobVacancy->title,
                'job_code'       => $application->jobVacancy->code,
                'status'         => $status,
                'employer_name'  => $application->jobVacancy->employer->user->name ?? 'Employer',
                'updated_at'     => now()->toDateTimeString(),
                'application_id' => $application->id,
                'action_type'    => $status,
            ];

            // Email
            SendApplicationStatusNotification::dispatch(
                $jobSeekerUser,
                'job_application_update',
                $title,
                $message,
                $data
            );

            // System notification
            AppHelper::systemNotificaiton(
                $jobSeekerUser,
                'job_application_update',
                $title,
                $message,
                $data
            );
        }
    }
}
