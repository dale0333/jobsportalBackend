<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Attribute, Category, JobVacancy};
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
}
