<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{JobVacancy, JobView, JobRating, JobApplication};
use App\Traits\ApiResponseTrait;
use App\Helpers\AppHelper;
use Illuminate\Support\Facades\Storage;

class JobSeekerController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage       = $request->input('per_page', 3);
            $search        = $request->input('search');
            $category      = $request->input('selectedCategory');
            $subCategories = $request->input('subCategories', []);
            $experience    = $request->input('experience');
            $sort          = $request->input('sort');
            $status        = $request->input('status', null);

            $query = JobVacancy::where('is_active', true)->with([
                'category',
                'jobLocation',
                'jobType',
                'jobQualify',
                'jobLevel',
            ]);

            // 🔍 Search by title or content
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            // 🗂️ Filter by Category
            if (!empty($category)) {
                $query->where('job_category', $category);
            }

            // 🧩 Filter by Subcategories
            if (!empty($subCategories)) {
                $query->where(function ($q) use ($subCategories) {
                    foreach ($subCategories as $sub) {
                        $q->orWhereJsonContains('job_sub_category', (int)$sub);
                    }
                });
            }

            // 💼 Filter by Experience
            if (!empty($experience)) {
                $query->where('job_experience', $experience);
            }

            // 🔽 Sorting
            switch ($sort) {
                case 'latest':
                    $query->latest();
                    break;
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

            if (!is_null($status)) {
                $query->where('is_active', $status);
            }

            // Pagination
            $data = $query->paginate($perPage);

            // Total displayed so far
            $displayed = $data->perPage() * ($data->currentPage() - 1) + count($data->items());

            // Transform data
            $formattedData = collect($data->items())->map(function ($item) {
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
                    'job_experience' => $item->job_experience,
                    'salary'         => $item->salary,
                    'views'          => $item->views ?? 0,
                    'average_rate'   => $item->ratings()->avg('rate') ?? 0,
                    'deadline'       => $item->deadline,
                    'post_at'        => $item->created_at,
                ];
            });

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
                "name" => "Tech Solutions Inc.",
                "logo" => "",
                "industry" => "Information Technology",
                "size" => "100-500 employees",
                "website" => "https://www.techsolutions.com",
                "location" => "123 Tech Street, Silicon Valley, CA",
                'founded' => 2010,
                'phone' => '+1 (555) 123-4567',
                'email' => 'test@mail.com',
                'telephone' => '+1 (555) 987-6543',
                "description" => "Tech Solutions Inc. is a leading provider of innovative technology solutions, specializing in software development, cloud computing, and IT consulting services. We are committed to delivering cutting-edge solutions that drive business success for our clients."
            ];

            $seekerRate = $request->user()
                ? optional(JobRating::where('job_id', $item->id)
                    ->where('user_id', $request->user()->id)
                    ->first())->rate ?? 0
                : 0;

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
                'job_experience' => $item->job_experience,
                'salary'       => $item->salary,
                'deadline'     => $item->deadline,
                'views'        => $item->views ?? 0,
                'average_rate' => number_format($item->ratings->avg('rate') ?? 0, 2),
                'post_at'      => $item->created_at,
                'company'      => $company,
                'seeker_rate' => $seekerRate,
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
                'job_id' => 'required|exists:job_vacancies,id',
                'coverLetter' => 'required|string|max:5000',
                'files.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            ]);

            // Create job application
            $application = JobApplication::create([
                'job_vacancy_id' => $request->job_id,
                'job_seeker_id' => $request->user()->id,
                'cover_letter' => $request->coverLetter,
                'status' => 'pending',
            ]);

            // Handle multiple file uploads (if any)
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $uniqueName = uniqid() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

                    // Define the folder path
                    $folder = 'attachments/job_applicant';

                    // Store file in the public disk
                    $path = $file->storeAs($folder, $uniqueName, 'public');

                    // Create attachment record
                    $application->attachments()->create([
                        'name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'type' => $file->getClientMimeType(),
                    ]);
                }
            }

            $data = [
                'application_id' => $application->id,
                'cover_letter' => $application->cover_letter,
                'files' => $application->attachments->map(fn($f) => [
                    'name' => $f->name,
                    'url' => Storage::url($f->file_path),
                ]),
            ];

            return $this->successResponse($data, 'Job application submitted successfully.');
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
}
