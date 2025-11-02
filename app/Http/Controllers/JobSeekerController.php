<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{JobVacancy, JobView, JobRating, JobApplication};
use App\Traits\ApiResponseTrait;
use App\Helpers\AppHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class JobSeekerController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $user = $request->user();

        try {
            $perPage       = $request->input('per_page', 3);
            $search        = $request->input('search');
            $category      = $request->input('selectedCategory');
            $subCategories = $request->input('subCategories', []);
            $experience    = $request->input('experience');
            $sort          = $request->input('sort');
            $status        = $request->input('status', null);

            $query = JobVacancy::where('is_active', true)
                ->whereDate('deadline', '>=', Carbon::today())
                ->with(['category', 'jobLocation', 'jobType', 'jobQualify', 'jobLevel', 'ratings', 'employer.user']);

            // 🧠 Personalized sorting: prioritize jobs matching seeker’s services
            $services = [];

            if (
                $user &&
                $user->user_type === 'job_seeker' &&
                empty($search) &&
                empty($category) &&
                empty($subCategories)
            ) {
                $services = $user->jobSeeker?->services ?? [];

                if (!empty($services)) {
                    $cases = [];
                    foreach ($services as $index => $serviceId) {
                        $id = (int)$serviceId;
                        $cases[] = "WHEN JSON_CONTAINS(job_sub_category, '{$id}') OR JSON_CONTAINS(job_sub_category, '\"{$id}\"') THEN {$index}";
                    }

                    $caseSql = 'CASE ' . implode(' ', $cases) . ' ELSE 9999 END';

                    $query->orderByRaw($caseSql)->orderByDesc('created_at');
                }
            }


            // 🔍 Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            // 🗂️ Category filter
            if (!empty($category)) {
                $query->where('job_category', $category);
            }

            // 🧩 Subcategory filter
            if (!empty($subCategories)) {
                $query->where(function ($q) use ($subCategories) {
                    foreach ($subCategories as $sub) {
                        $q->orWhereJsonContains('job_sub_category', (int)$sub);
                    }
                });
            }

            // 💼 Experience filter
            if (!empty($experience)) {
                $query->where('job_experience', $experience);
            }

            // 🔽 Sorting
            if (!empty($sort)) {
                switch ($sort) {
                    case 'oldest':
                        $query->oldest();
                        break;
                    case 'salary_high':
                        $query->orderByRaw("CAST(REPLACE(REPLACE(salary, '₱', ''), ',', '') AS UNSIGNED) DESC");
                        break;
                    case 'salary_low':
                        $query->orderByRaw("CAST(REPLACE(REPLACE(salary, '₱', ''), ',', '') AS UNSIGNED) ASC");
                        break;
                    default:
                        $query->latest();
                }
            } else {
                $query->latest();
            }

            // 🔘 Status filter
            if (!is_null($status)) {
                $query->where('is_active', $status);
            }

            // 📄 Pagination
            $data = $query->paginate($perPage);

            // 🧮 Compute how many items displayed so far
            $displayed = $data->perPage() * ($data->currentPage() - 1) + count($data->items());

            // 🧾 Transform data
            $formattedData = collect($data->items())->map(function ($item) use ($request) {
                return [
                    'id'             => $item->id,
                    'title'          => $item->title,
                    'code'           => $item->code,
                    'content'        => $item->content,
                    'category'       => optional($item->category)->name,
                    'sub_categories' => AppHelper::getSubCategoryNames($item->job_sub_category),
                    'job_location'   => optional($item->jobLocation)->name,
                    'job_type'       => optional($item->jobType)->name,
                    'job_qualify'    => optional($item->jobQualify)->name,
                    'job_level'      => optional($item->jobLevel)->name,
                    'available'      => $item->available,
                    'job_experience' => $item->job_experience,
                    'salary'         => $item->salary,
                    'views'          => $item->views ?? 0,
                    'average_rate'   => number_format($item->ratings->avg('rate') ?? 0, 2),
                    'deadline'       => $item->deadline,
                    'post_at'        => $item->created_at,
                    'seeker_rate'    => $this->jobRating($item, $request),
                    'is_applied'     => $this->checkApplied($item, $request),
                    'company'        => $item->employer->user,
                ];
            });

            // ✅ Include displayed count in response
            return response()->json([
                'data'         => $formattedData,
                'total'        => $data->total(),
                'displayed'    => $displayed,
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch jobs', 500, $e->getMessage());
        }
    }

    public function show(Request $request, string $code)
    {
        try {
            $item = JobVacancy::with([
                'jobLocation',
                'jobType',
                'jobQualify',
                'jobLevel',
                'views',
                'ratings',
                'employer'
            ])->where('code', $code)->first();

            if (!$item) {
                return $this->errorResponse('Job not found', 404);
            }

            // Register view by jobseeker
            if ($request->user()) {
                JobView::firstOrCreate([
                    'job_id' => $item->id,
                    'user_id' => $request->user()->id,
                ]);

                $item->views = $item->views()->count();
                $item->save();
            }

            $company = [
                "name" => $item->employer->user->name,
                "avatar" => $item->employer->user->avatar,
                "cover_photo" => $item->employer->user->cover_photo,
                "industry" => $item->employer->industry,
                "size" => $item->employer->company_size,
                "website" =>  $item->employer->user->socialMedias,
                "location" => $item->employer->user->address,
                'telephone' => $item->employer->user->telephone,
                'email' => $item->employer->user->email,
                "description" => $item->employer->user->bio
            ];

            // Prepare response data
            $data = [
                'id'           => $item->id,
                'title'        => $item->title,
                'code'         => $item->code,
                'content'      => $item->content,

                'category'     => optional($item->category)->name,
                'sub_categories' => AppHelper::getSubCategoryNames($item->job_sub_category),
                'job_location' => optional($item->jobLocation)->name,
                'job_type'     => optional($item->jobType)->name,
                'job_qualify'  => optional($item->jobQualify)->name,
                'job_level'    => optional($item->jobLevel)->name,

                'available'      => $item->available,
                'job_experience' => $item->job_experience,
                'salary'       => $item->salary,
                'deadline'     => $item->deadline,
                'views'        => $item->views ?? 0,
                'average_rate' => number_format($item->ratings->avg('rate') ?? 0, 2),
                'post_at'      => $item->created_at,
                'company'      => $company,
                'seeker_rate'  => $this->jobRating($item, $request),
                'is_applied'   => $this->checkApplied($item, $request),
            ];


            return $this->successResponse($data, 'Job retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch job', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'job_id'       => 'required|exists:job_vacancies,id',
                'coverLetter'  => 'required|string|max:5000',
                'files.*'      => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            ]);

            $user = $request->user();

            // ✅ Create job application
            $application = JobApplication::create([
                'job_vacancy_id' => $request->job_id,
                'job_seeker_id'  => $user->jobSeeker?->id,
                'cover_letter'   => $request->coverLetter,
                'status'         => 'pending',
            ]);

            // ✅ Handle attachments
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {

                    $uniqueName = uniqid() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                    $folder = 'attachments/job_applicant';
                    $path = $file->storeAs($folder, $uniqueName, 'public');

                    $application->attachments()->create([
                        'name'      => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'type'      => $file->getClientMimeType(),
                    ]);
                }
            }

            $application->load(['attachments', 'jobVacancy.employer.user']);

            $data = [
                'application_id' => $application->id,
                'cover_letter'   => $application->cover_letter,
                'files' => $application->attachments->map(fn($f) => [
                    'name' => $f->name,
                    'url'  => Storage::url($f->file_path),
                ]),
            ];

            // ------------------------------------------------------
            // ✅ Stored Notification: Employer (NO email)
            // ------------------------------------------------------
            $employerUser = $application->jobVacancy->employer->user ?? null;

            if ($employerUser) {
                AppHelper::storedNotification(
                    $employerUser,
                    'job_application',
                    'New Job Application Received',
                    "{$user->name} has applied for your job post '{$application->jobVacancy->title}'.",
                    [
                        'job_vacancy'      => $application->jobVacancy->title,
                        'application_code' => $application->jobVacancy->code,
                        'applicant_name'   => $user->name,
                        'cover_letter'     => $application->cover_letter ?? 'No cover letter provided',
                    ]
                );
            }

            // ------------------------------------------------------
            // ✅ Email Notification: Applicant (Job Seeker)
            // ------------------------------------------------------
            AppHelper::sendNotificationEmail(
                $user,
                'job_application',
                'Your Job Application Was Submitted',
                "Your application for '{$application->jobVacancy->title}' was submitted successfully.",
                [
                    'job_vacancy'      => $application->jobVacancy->title,
                    'application_code' => $application->jobVacancy->code,
                    'applicant_name'   => $user->name,
                ]
            );

            return $this->successResponse($data, 'Job application submitted successfully.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to submit job application', 500, $e->getMessage());
        }
    }


    public function update(Request $request, string $code)
    {
        try {
            $job = JobVacancy::where('code', $code)->first();

            if (!$job) {
                return $this->errorResponse('Job not found', 404);
            }

            $rate = $request->input('rate');

            if (!$rate || $rate < 1 || $rate > 5) {
                return $this->errorResponse('Invalid rating value. Must be between 1 and 5.', 400);
            }

            JobRating::updateOrCreate(
                [
                    'job_id' => $job->id,
                    'user_id' => $request->user()->id,
                ],
                [
                    'rate' => $rate,
                ]
            );

            $averageRate = JobRating::where('job_id', $job->id)->avg('rate') ?? 0;

            $job->rates = $averageRate;
            $job->save();

            $data = [
                'job_id' => $job->id,
                'average_rate' => round($averageRate, 1),
            ];

            return $this->successResponse($data, 'Job rated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update job rating', 500, $e->getMessage());
        }
    }

    private function jobRating($item, Request $request)
    {
        return $request->user()
            ? optional(
                JobRating::where('job_id', $item->id)
                    ->where('user_id', $request->user()->id)
                    ->first()
            )->rate ?? 0
            : 0;
    }

    private function checkApplied($item, Request $request)
    {
        return $request->user()
            ? JobApplication::where('job_vacancy_id', $item->id)
            ->where('job_seeker_id', optional($request->user()->jobSeeker)->id)
            ->exists()
            : false;
    }
}
