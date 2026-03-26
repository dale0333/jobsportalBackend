<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Models\{JobApplication};

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

            $request->validate([
                'type'     => 'required|string',
                'status'   => 'nullable|integer',
                'selected' => 'nullable|in:accept,decline',
                'notes'    => 'nullable|string|max:1000',
            ]);

            $application = JobApplication::with([
                'jobSeeker.user',
                'jobVacancy.employer.user'
            ])
                ->where('job_seeker_id', $user->jobSeeker->id)
                ->where('id', $id)
                ->first();

            if (!$application) {
                return $this->errorResponse('Job application not found.', 404);
            }

            $action = $request->selected;

            // 🔹 Handle invited → applied / matched
            if ($request->type === 'invited' && $request->status == '0') {
                $application->update([
                    'type' => $action === 'accept' ? 'applied' : 'matched',
                ]);
            }

            // 🔹 Handle interview response
            if ($request->type === 'applied' && $request->status == '1') {
                $application->update([
                    'is_accepted' => $action === 'accept' ? 1 : 2,
                ]);
            }

            // ✅ Store transaction history (FIXED)
            $application->jobApplicationTransactions()->create([
                'process_by' => $user->id,
                'notes'      => $request->notes ?? "Application {$action} by job seeker",
                'status'     => $action === 'decline' ? 6 : $request->status,
            ]);

            // ✅ Notification
            $this->storeUpdateNotification($application, $action);

            // ✅ Activity log
            AppHelper::userLog(
                $user->id,
                ucfirst($action) . " Job Application '{$application->id}'"
            );

            return $this->successResponse(
                $application,
                "Application {$action} successfully!"
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to process application.',
                500,
                $e->getMessage()
            );
        }
    }

    // Private method for update notifications
    private function storeUpdateNotification($application, $type)
    {
        $jobSeekerUser = $application->jobSeeker->user ?? null;
        $employerUser = $application->jobVacancy->employer->user ?? null;

        $jobTitle = $application->jobVacancy->title ?? 'Unknown Job';
        $companyName = $application->jobVacancy->employer->user->name ?? 'Unknown Company';
        $applicantName = $jobSeekerUser->name ?? 'Unknown Applicant';

        $title = "Application Update";
        $statusLabel = ucfirst($type);

        $actionText = match ($type) {
            'accept' => 'Accepted',
            'decline' => 'Declined',
            default => $type,
        };

        // ------------------------------
        // Employer notification
        // ------------------------------
        if ($employerUser) {

            $message = "{$applicantName} marked their application as {$statusLabel} for '{$jobTitle}'";

            AppHelper::storedNotification(
                $employerUser,
                'application_update',
                $title,
                $message,
                [
                    'job_vacancy' => $jobTitle,
                    'applicant_name' => $applicantName,
                    'status' => $actionText,
                ]
            );
        }

        // ------------------------------
        // Job Seeker notification
        // ------------------------------
        if ($jobSeekerUser) {

            $message = "Your application for '{$jobTitle}' at {$companyName} was marked as {$statusLabel}.";

            AppHelper::systemNotificaiton(
                $jobSeekerUser,
                'application_update',
                $title,
                $message,
                [
                    'job_title' => $jobTitle,
                    'company_name' => $companyName,
                    'status' => $actionText,
                ]
            );

            SendApplicationStatusNotification::dispatch(
                $jobSeekerUser,
                'application_update',
                $title,
                $message,
                [
                    'job_title' => $jobTitle,
                    'company_name' => $companyName,
                    'status' => $actionText,
                ]
            );
        }
    }
}
