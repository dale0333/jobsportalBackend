<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Attribute, Category, JobVacancy, Employer, JobApplication, JobFavorite, Notification};
use App\Helpers\AppHelper;
use Carbon\Carbon;
use App\Traits\ApiResponseTrait;

class FetchServiceController extends Controller
{
    use ApiResponseTrait;
    public function fetchAttributes()
    {
        try {
            $data = Attribute::with('subAttributes')->get();
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function fetchCategories()
    {
        try {
            $data = Category::with('subCategories')->get();
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function viewJobs(Request $request)
    {
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

            // 🔍 Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
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
                    'qualifications' => $item->qualifications,
                    'description'    => $item->description,

                    'category'     => optional($item->category)->name,
                    'sub_categories' => AppHelper::getSubCategoryNames($item->job_sub_category),
                    'job_location' => optional($item->jobLocation)->name,
                    'job_type'     => optional($item->jobType)->name,
                    'job_qualify'  => optional($item->jobQualify)->name,
                    'job_level'    => optional($item->jobLevel)->name,
                    'job_experience' => optional($item->jobExperience)->name,

                    'available'      => $item->available,
                    'salary'         => $item->salary,
                    'views'          => $item->views ?? 0,
                    'average_rate'   => number_format($item->ratings->avg('rate') ?? 0, 2),
                    'deadline'       => $item->deadline,
                    'post_at'        => $item->created_at,
                    'company'        => $item->employer->user,
                ];
            });

            $data = ([
                'items'         => $formattedData,
                'total'        => $data->total(),
                'displayed'    => $displayed,
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
            ]);

            return $this->successResponse($data, 'Job retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch jobs', 500, $e->getMessage());
        }
    }

    public function viewJobDetails($code)
    {
        try {
            $item = JobVacancy::with([
                'jobLocation',
                'jobType',
                'jobQualify',
                'jobLevel',
                'jobExperience',
                'views',
                'ratings',
                'employer'
            ])->where('code', $code)->first();

            if (!$item) {
                return $this->errorResponse('Job not found', 404);
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
                'qualifications' => $item->qualifications,
                'description'    => $item->description,

                'category'     => optional($item->category)->name,
                'sub_categories' => AppHelper::getSubCategoryNames($item->job_sub_category),
                'job_location' => optional($item->jobLocation)->name,
                'job_type'     => optional($item->jobType)->name,
                'job_qualify'  => optional($item->jobQualify)->name,
                'job_level'    => optional($item->jobLevel)->name,
                'job_experience' => optional($item->jobExperience)->name,

                'available'      => $item->available,
                'salary'       => $item->salary,
                'deadline'     => $item->deadline,
                'views'        => $item->views ?? 0,
                'average_rate' => number_format($item->ratings->avg('rate') ?? 0, 2),
                'post_at'      => $item->created_at,
                'company'      => $company,
            ];


            return $this->successResponse($data, 'Job retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch job', 500, $e->getMessage());
        }
    }

    public function viewCategories()
    {
        try {
            $categories = Category::where('is_active', 1)
                ->select('id', 'name', 'slug', 'icon', 'description')
                ->withCount('jobVacancies')
                ->orderBy('job_vacancies_count', 'desc')
                ->limit(7)
                ->get();

            return $this->successResponse($categories, 'Categories retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch categories', 500, $e->getMessage());
        }
    }

    // data request
    public function requestData($type, Request $request)
    {
        try {

            switch ($type) {

                case 'jobs':
                    $empId     = $request->emp_id;
                    $dateRange = $request->dateRange;

                    $dateStart = null;
                    $dateEnd   = null;

                    if (!empty($dateRange)) {
                        $dates = explode(' to ', $dateRange);

                        if (count($dates) === 2) {
                            $dateStart = Carbon::parse(trim($dates[0]))->startOfDay();
                            $dateEnd   = Carbon::parse(trim($dates[1]))->endOfDay();
                        }
                    }

                    $query = JobVacancy::where('employer_id', $empId);

                    if ($dateStart && $dateEnd) {
                        $query->whereBetween('created_at', [$dateStart, $dateEnd]);
                    }

                    $data = $query->get();
                    break;

                case 'employer':
                    $data = Employer::with('user')->get();
                    break;

                case 'favorite':

                    // ensure user is logged in
                    if (!$request->user()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Unauthorized'
                        ], 401);
                    }

                    $vacancy  = JobVacancy::where('code', $request->job_code)->first();
                    $userId = $request->user()->id;

                    $favorite = JobFavorite::where('job_id', $vacancy->id)
                        ->where('user_id', $userId)
                        ->first();

                    if ($favorite) {
                        // toggle favorite
                        $favorite->is_favorite = !$favorite->is_favorite;
                        $favorite->save();
                    } else {
                        // create favorite entry
                        $favorite = JobFavorite::create([
                            'job_id'      => $vacancy->id,
                            'user_id'     => $userId,
                            'is_favorite' => true,
                        ]);
                    }

                    $data = ([
                        'success'     => true,
                        'message'     => $favorite->is_favorite
                            ? 'Added to favorites'
                            : 'Removed from favorites',
                        'is_favorite' => $favorite->is_favorite,
                    ]);

                    break;

                case 'dash_seeker':
                    $user = $request->user();

                    // --- Vacancies ---
                    // $vacancyQuery = JobVacancy::with([
                    //     'category',
                    //     'jobLocation',
                    //     'jobType',
                    //     'jobQualify',
                    //     'jobLevel',
                    //     'jobExperience',
                    //     'employer.user'
                    // ])->where('is_active', true);

                    // Optional: sort by seeker services
                    // $services = $user->jobSeeker?->services ?? [];
                    // if (!empty($services)) {
                    //     $cases = [];
                    //     $params = [];
                    //     foreach ($services as $index => $serviceId) {
                    //         $id = (int)$serviceId;
                    //         $cases[] = "WHEN JSON_CONTAINS(job_sub_category, ?) THEN ?";
                    //         $params[] = "\"{$id}\"";
                    //         $params[] = $index;
                    //     }
                    //     $caseSql = "CASE " . implode(' ', $cases) . " ELSE 9999 END";
                    //     $vacancyQuery->orderByRaw($caseSql, $params);
                    // }
                    // $vacancyQuery->latest();
                    // $vacancies = $vacancyQuery->limit(5)->get();
                    // $totalVacancies = $vacancyQuery->count();

                    // --- Invited ---
                    $invitedQuery = JobApplication::where('job_seeker_id', $user->jobSeeker->id)
                        ->where('type', 'invited')
                        ->with(['jobVacancy.employer.user'])
                        ->latest();

                    $invited = $invitedQuery->limit(5)->get();
                    $totalInvited = $invitedQuery->count();

                    // --- Applications ---
                    $applicationsQuery = JobApplication::where('job_seeker_id', $user->jobSeeker->id)
                        ->where('status', '!=', 1)
                        ->with(['jobVacancy.employer.user'])
                        ->latest();

                    $applications = $applicationsQuery->limit(5)->get();
                    $totalApplications = $applicationsQuery->count();

                    // --- Interviews ---
                    $interviewsQuery = JobApplication::where('job_seeker_id', $user->jobSeeker->id)
                        ->where('status', '1')
                        ->with(['jobVacancy.employer.user'])
                        ->latest();

                    $interviews = $interviewsQuery->limit(5)->get();
                    $totalInterviews = $interviewsQuery->count();

                    // --- Notifications ---
                    $notificationsQuery = Notification::where('user_id', $user->id)->latest();
                    $notifications = $notificationsQuery->limit(6)->get();
                    $totalNotifications = $notificationsQuery->count();

                    // --- Final Data ---
                    $data = [
                        'invites' => $invited,
                        'applications' => $applications,
                        'interviews' => $interviews,
                        'notifications' => $notifications,

                        'total_invites' => $totalInvited,
                        'total_applications' => $totalApplications,
                        'total_interviews' => $totalInterviews,
                        'total_notifications' => $totalNotifications,
                    ];
                    break;

                default:
                    return $this->errorResponse("Invalid request type", 400);
            }

            return $this->successResponse($data, 'Data retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch data', 500, $e->getMessage());
        }
    }
}
