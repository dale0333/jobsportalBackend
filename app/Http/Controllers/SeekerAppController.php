<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Models\{JobApplication};
use Illuminate\Support\Facades\DB;

use App\Helpers\AppHelper;
use App\Jobs\SendApplicationStatusNotification;

class SeekerAppController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');
            $status = $request->input('status');
            $type = $request->input('type');

            $user = $request->user();

            $query = JobApplication::with([
                'jobSeeker.user',
                'jobVacancy.employer.user',
                'jobVacancy.category',
                'jobVacancy.jobLocation',
                'jobVacancy.jobType',
                'jobVacancy.jobQualify',
                'jobVacancy.jobLevel',
                'jobVacancy.jobExperience',
                'attachments',
                'jobApplicationTransactions.processedBy'
            ])
                ->where('job_seeker_id', $user->jobSeeker->id);

            // Status Filter
            if ($type === 'applied') {
                $query->where('type', 'applied');
            } elseif ($type === 'invited') {
                $query->where('type', 'invited');
            } else {
                $query->where('type', $type);
            }

            if (!empty($status)) {
                $query->where('status', $status);
            }

            // Search Filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('jobVacancy', function ($vacancyQuery) use ($search) {
                        $vacancyQuery->where('title', 'like', "%{$search}%")
                            ->orWhereHas('employer.user', function ($employerQuery) use ($search) {
                                $employerQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('company_name', 'like', "%{$search}%");
                            });
                    });
                });
            }

            // Pagination
            $data = $query->latest()->paginate($perPage);

            // Count by types
            $countTypes = [
                'applied' => JobApplication::where('job_seeker_id', $user->jobSeeker->id)
                    ->where('type', 'applied')
                    ->count(),
                'invited' => JobApplication::where('job_seeker_id', $user->jobSeeker->id)
                    ->where('type', 'invited')
                    ->count(),
            ];

            /**
             * Final response
             */
            return $this->successResponse([
                'items' => $data->items(),
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'count_types' => $countTypes,
            ], 'Job applications fetched successfully!');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch job applications', 500, $e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        try {
            $user = $request->user();

            $application = JobApplication::with([
                'jobSeeker.user',
                'jobVacancy.employer.user'
            ])->where('job_seeker_id', $user->jobSeeker->id)
                ->where('id', $id)
                ->first();

            if (!$application) {
                return $this->errorResponse('Job application not found.', 404);
            }

            // Update application status to withdrawn
            $application->update([
                'status' => 'withdrawn',
            ]);

            // Create a transaction record
            $application->jobApplicationTransactions()->create([
                'process_by' => $user->id,
                'notes' => 'Application withdrawn by job seeker',
                'status' => 'withdrawn',
            ]);

            // ✅ Send notifications to both employer and job seeker
            $this->storeWithdrawalNotification($application);

            // ✅ Activity log
            AppHelper::userLog(
                $user->id,
                "Withdrawn Job Application '{$application->id}'"
            );

            return $this->successResponse(null, 'Job application withdrawn successfully!');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to withdraw job application.', 500, $e->getMessage());
        }
    }

    // Private method for withdrawal notifications
    private function storeWithdrawalNotification($application)
    {
        $jobSeekerUser = $application->jobSeeker->user ?? null;
        $employerUser = $application->jobVacancy->employer->user ?? null;

        $jobTitle = $application->jobVacancy->title ?? 'Unknown Job';
        $companyName = $application->jobVacancy->employer->user->company_name ?? 'Unknown Company';
        $applicantName = $jobSeekerUser->name ?? 'Unknown Applicant';

        // -----------------------------------------
        // Employer notification (System)
        // -----------------------------------------
        if ($employerUser) {
            $employerTitle = "Application Withdrawn";
            $employerMessage = "{$applicantName} has withdrawn their application for '{$jobTitle}'";

            // System notification for employer
            AppHelper::storedNotification(
                $employerUser,
                'application_withdrawn',
                $employerTitle,
                $employerMessage,
                [
                    'job_vacancy' => $jobTitle,
                    'application_code' => $application->jobVacancy->code ?? 'N/A',
                    'applicant_name' => $applicantName,
                    'status' => 'withdrawn',
                    'withdrawn_at' => now()->toDateTimeString(),
                    'application_id' => $application->id,
                ]
            );
        }

        // -----------------------------------------
        // Job Seeker notification (System + Email)
        // -----------------------------------------
        if ($jobSeekerUser) {
            $seekerTitle = "Application Withdrawn";
            $seekerMessage = "You have successfully withdrawn your application for '{$jobTitle}' at {$companyName}";

            // System notification for job seeker
            AppHelper::systemNotificaiton(
                $jobSeekerUser,
                'application_withdrawn',
                $seekerTitle,
                $seekerMessage,
                [
                    'job_title' => $jobTitle,
                    'company_name' => $companyName,
                    'status' => 'withdrawn',
                    'withdrawn_at' => now()->toDateTimeString(),
                    'application_id' => $application->id,
                    'action_type' => 'withdrawn',
                ]
            );

            // Email notification for job seeker
            SendApplicationStatusNotification::dispatch(
                $jobSeekerUser,
                'application_withdrawn',
                $seekerTitle,
                $seekerMessage,
                [
                    'job_title' => $jobTitle,
                    'company_name' => $companyName,
                    'status' => 'withdrawn',
                    'withdrawn_at' => now()->toDateTimeString(),
                    'application_id' => $application->id,
                    'action_type' => 'withdrawn',
                ]
            );
        }
    }
}
