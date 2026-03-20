<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{JobVacancy, ReferenceDetail, Notification, JobApplication, User, Category, Employer, Reference};
use Carbon\Carbon;
use App\Traits\ApiResponseTrait;
use Barryvdh\DomPDF\Facade\Pdf;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\{ReferenceDetailsExport, ReferenceEmployerExport};

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function getAvailableYears()
    {
        try {
            // Get years from job applications
            $applicationYears = JobApplication::selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            // Get years from job vacancies
            $vacancyYears = JobVacancy::selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            // Merge and get unique years
            $allYears = array_unique(array_merge($applicationYears, $vacancyYears));
            rsort($allYears);

            // If no data exists, return current year
            if (empty($allYears)) {
                $allYears = [Carbon::now()->year];
            }

            return response()->json([
                'years' => $allYears
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch available years',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getJobWidgets(Request $request)
    {
        try {
            $user = $request->user();
            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;
            $lastMonth = Carbon::now()->copy()->subMonth()->month;

            // Determine if filtering by employer
            $employerId = null;
            if ($user && $user->user_type === 'employer' && $user->employer) {
                $employerId = $user->employer->id;
            }

            // Base queries
            $jobQuery = JobVacancy::query();
            $applicationQuery = JobApplication::query();

            // 🔒 Filter by employer if not admin
            if ($employerId) {
                $jobQuery->where('employer_id', $employerId);
                $applicationQuery->whereHas('jobVacancy', function ($q) use ($employerId) {
                    $q->where('employer_id', $employerId);
                });
            }

            // Total Jobs
            $totalJobs = $jobQuery->count();

            // Total Applications
            $totalApplications = $applicationQuery->count();

            // New Jobs (current month)
            $newJobs = (clone $jobQuery)
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->count();

            // Interview applications
            $interview = (clone $applicationQuery)
                ->where('status', 1)
                ->count();

            // Hired applications
            $hired = (clone $applicationQuery)
                ->where('status', 2)
                ->count();

            // Rejected applications
            $rejected = (clone $applicationQuery)
                ->where('status', 3)
                ->count();

            // Growth percentages
            $totalJobsPercentage = $this->calculateGrowthPercentage(
                (clone $jobQuery)->whereYear('created_at', $currentYear)->whereMonth('created_at', $currentMonth)->count(),
                (clone $jobQuery)->whereYear('created_at', $currentYear)->whereMonth('created_at', $lastMonth)->count()
            );

            $totalApplicationsPercentage = $this->calculateGrowthPercentage(
                (clone $applicationQuery)->whereYear('created_at', $currentYear)->whereMonth('created_at', $currentMonth)->count(),
                (clone $applicationQuery)->whereYear('created_at', $currentYear)->whereMonth('created_at', $lastMonth)->count()
            );

            $newJobsPercentage = $this->calculateGrowthPercentage(
                (clone $jobQuery)->whereYear('created_at', $currentYear)->whereMonth('created_at', $currentMonth)->count(),
                (clone $jobQuery)->whereYear('created_at', $currentYear)->whereMonth('created_at', $lastMonth)->count()
            );

            // Status percentages relative to total applications
            $interviewPercentage = $this->calculatePercentage($interview, $totalApplications);
            $hiredPercentage = $this->calculatePercentage($hired, $totalApplications);
            $rejectedPercentage = $this->calculatePercentage($rejected, $totalApplications);

            return response()->json([
                'totalJobs' => round($totalJobs),
                'totalJobsPercentage' => round(min($totalJobsPercentage, 100)),
                'totalApplications' => round($totalApplications),
                'totalApplicationsPercentage' => round(min($totalApplicationsPercentage, 100)),
                'newJobs' => round($newJobs),
                'newJobsPercentage' => round(min($newJobsPercentage, 100)),
                'interview' => round($interview),
                'interviewPercentage' => round(min($interviewPercentage, 100)),
                'hired' => round($hired),
                'hiredPercentage' => round(min($hiredPercentage, 100)),
                'rejected' => round($rejected),
                'rejectedPercentage' => round(min($rejectedPercentage, 100)),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch job widgets data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateGrowthPercentage($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        $growth = (($current - $previous) / $previous) * 100;
        return max(0, min($growth, 100));
    }

    private function calculatePercentage($part, $total)
    {
        if ($total == 0) {
            return 0;
        }

        return ($part / $total) * 100;
    }

    // Analytic 1 =============================================================
    public function getAnalytic1(Request $request)
    {
        try {
            $user = $request->user();

            // Determine employer_id filter
            $employerId = null;
            if ($user && $user->user_type === 'employer' && $user->employer) {
                $employerId = $user->employer->id;
            }

            // Get date range from request or default to last 30 days
            $startDate = $request->input('start_date', Carbon::now()->subDays(30));
            $endDate = $request->input('end_date', Carbon::now());

            // Previous period for growth calculation
            $previousStartDate = Carbon::parse($startDate)->subDays(30);
            $previousEndDate = Carbon::parse($startDate);

            // === Applications ===
            $applicationQuery = JobApplication::where('type', 'applied');
            if ($employerId) {
                $applicationQuery->whereHas('jobVacancy', function ($q) use ($employerId) {
                    $q->where('employer_id', $employerId);
                });
            }

            // Total Applications (all time)
            $totalApplications = (clone $applicationQuery)->count();

            // Applications growth (current period vs previous period)
            $currentPeriodApplications = (clone $applicationQuery)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $previousPeriodApplications = (clone $applicationQuery)
                ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                ->count();

            $applicationGrowth = $this->calculateGrowthRate(
                $currentPeriodApplications,
                $previousPeriodApplications
            );

            // === Job Vacancies ===
            $jobQuery = JobVacancy::where('is_active', true);
            if ($employerId) {
                $jobQuery->where('employer_id', $employerId);
            }

            $totalJobs = (clone $jobQuery)->count();

            $currentPeriodJobs = (clone $jobQuery)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $previousPeriodJobs = (clone $jobQuery)
                ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                ->count();

            $jobGrowth = $this->calculateGrowthRate(
                $currentPeriodJobs,
                $previousPeriodJobs
            );

            // === Hired Candidates ===
            $hiredQuery = JobApplication::where('status', 1);
            if ($employerId) {
                $hiredQuery->whereHas('jobVacancy', function ($q) use ($employerId) {
                    $q->where('employer_id', $employerId);
                });
            }
            $totalHired = $hiredQuery->count();

            // Hire rate
            $processedQuery = JobApplication::whereIn('status', [1, 3]);
            if ($employerId) {
                $processedQuery->whereHas('jobVacancy', function ($q) use ($employerId) {
                    $q->where('employer_id', $employerId);
                });
            }
            $totalProcessedApplications = $processedQuery->count();

            $hireRate = $totalProcessedApplications > 0
                ? round(($totalHired / $totalProcessedApplications) * 100, 2)
                : 0;

            // === Employers (only for admin views) ===
            $activeEmployers = 0;
            $employerGrowth = 0;

            if (!$employerId) {
                $activeEmployers = User::where('user_type', 'employer')
                    ->where('is_active', true)
                    ->count();

                $currentPeriodEmployers = User::where('user_type', 'employer')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $previousPeriodEmployers = User::where('user_type', 'employer')
                    ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                    ->count();

                $employerGrowth = $this->calculateGrowthRate(
                    $currentPeriodEmployers,
                    $previousPeriodEmployers
                );
            }

            // === Data Output ===
            $data = [
                'items' => [
                    'totalApplications' => $totalApplications,
                    'totalJobs' => $totalJobs,
                    'totalHired' => $totalHired,
                    'activeEmployers' => $activeEmployers,
                    'applicationGrowth' => $applicationGrowth,
                    'jobGrowth' => $jobGrowth,
                    'hireRate' => $hireRate,
                    'employerGrowth' => $employerGrowth,
                ],
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'previous_start_date' => $previousStartDate,
                    'previous_end_date' => $previousEndDate,
                ]
            ];

            return $this->successResponse($data, 'Fetched successfully!', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse(
                'Failed to fetch analytics data. ' . $e->getMessage(),
                500
            );
        }
    }

    public function getAnalytic2(Request $request)
    {
        try {
            $year = (int) $request->get('year', now()->year);
            $user = $request->user();

            // Validate year (simple range check)
            if ($year < 2000 || $year > now()->year + 1) {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid year provided.'
                ], 400);
            }

            $employerId = null;
            if ($user && $user->user_type === 'employer' && $user->employer) {
                $employerId = $user->employer->id;
            }

            // Get analytics data
            $monthlyData = $this->getYearlyStats($year, $employerId);
            $summaryData = $this->getYearlySummary($year, $employerId);

            $data = ([
                'items' => [
                    'monthly' => $monthlyData,
                    'summary' => $summaryData,
                    'year' => $year
                ]
            ]);

            return $this->successResponse($data, 'fetch successfully!', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to fetch analytics data. ' . $e->getMessage(), 500);
        }
    }

    public function getAnalytic3(Request $request, $year = null)
    {
        try {
            $year = $year ?? Carbon::now()->year;
            $user = $request->user();

            // Determine employer_id filter
            $employerId = null;
            if ($user && $user->user_type === 'employer' && $user->employer) {
                $employerId = $user->employer->id;
            }

            // Monthly applications data
            $monthlyApplications = [];
            for ($month = 1; $month <= 12; $month++) {
                $query = JobApplication::where('type', 'applied')
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month);

                if ($employerId) {
                    $query->whereHas('jobVacancy', function ($q) use ($employerId) {
                        $q->where('employer_id', $employerId);
                    });
                }

                $monthlyApplications[] = $query->count();
            }

            // Application status counts
            $statuses = [1, 2, 3, 4, 5];
            $applicationStatus = [];

            foreach ($statuses as $status) {
                $query = JobApplication::where('status', $status)->where('type', 'applied');
                if ($employerId) {
                    $query->whereHas('jobVacancy', function ($q) use ($employerId) {
                        $q->where('employer_id', $employerId);
                    });
                }
                $applicationStatus[$status] = $query->count();
            }

            // Additional statistics
            $totalApplications = array_sum($monthlyApplications);
            $hireRate = $totalApplications > 0
                ? round(($applicationStatus[1] / $totalApplications) * 100, 2)
                : 0;

            $data = [
                'items' => [
                    'year' => $year,
                    'monthly_applications' => $monthlyApplications,
                    'application_status' => $applicationStatus,
                    'total_applications' => $totalApplications,
                    'hire_rate' => $hireRate,
                    'summary' => [
                        'average_monthly' => round($totalApplications / 12, 2),
                        'peak_month' => $this->getPeakMonth($monthlyApplications),
                        'growth_rate' => $this->calculateYearlyGrowth($year),
                    ],
                ],
            ];

            return $this->successResponse($data, 'Fetch successful!', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse(
                'Failed to fetch analytics data. ' . $e->getMessage(),
                500
            );
        }
    }

    public function getAnalytic4(Request $request)
    {
        try {
            $year = $request->get('year', now()->year);
            $currentMonth = now()->month;
            $lastMonth = $currentMonth - 1;
            $lastMonthYear = $year;

            // Handle January edge case
            if ($lastMonth === 0) {
                $lastMonth = 12;
                $lastMonthYear = $year - 1;
            }

            $categories = Category::where('is_active', true)
                ->withCount([
                    'jobVacancies as total_jobs' => function ($q) use ($year, $currentMonth) {
                        $q->whereYear('created_at', $year)
                            ->whereMonth('created_at', $currentMonth)
                            ->where('is_active', true);
                    },
                    'jobVacancies as last_month_jobs' => function ($q) use ($lastMonthYear, $lastMonth) {
                        $q->whereYear('created_at', $lastMonthYear)
                            ->whereMonth('created_at', $lastMonth)
                            ->where('is_active', true);
                    },
                ])
                ->get()
                ->map(function ($category) use ($year, $currentMonth, $lastMonthYear, $lastMonth) {
                    // Applicants for current and previous month
                    $currentApplicants = JobApplication::whereHas('jobVacancy', function ($q) use ($category, $year, $currentMonth) {
                        $q->where('job_category', $category->id)
                            ->where('type', 'applied')
                            ->whereYear('created_at', $year)
                            ->whereMonth('created_at', $currentMonth);
                    })->count();

                    $lastMonthApplicants = JobApplication::whereHas('jobVacancy', function ($q) use ($category, $lastMonthYear, $lastMonth) {
                        $q->where('job_category', $category->id)
                            ->where('type', 'applied')
                            ->whereYear('created_at', $lastMonthYear)
                            ->whereMonth('created_at', $lastMonth);
                    })->count();

                    // ✅ Improved growth calculation
                    $previous = $category->last_month_jobs ?? 0;
                    $current = $category->total_jobs ?? 0;

                    if ($previous == 0 && $current == 0) {
                        $jobsGrowth = 0;
                    } elseif ($previous == 0 && $current > 0) {
                        $jobsGrowth = 100;
                    } else {
                        $jobsGrowth = (($current - $previous) / $previous) * 100;
                    }

                    return [
                        'id' => $category->id,
                        'category' => $category->name,
                        'description' => $category->description,
                        'total_jobs' => $current,
                        'total_applicants' => $currentApplicants,
                        'growth' => round($jobsGrowth, 1),
                        'last_month_jobs' => $previous,
                        'last_month_applicants' => $lastMonthApplicants,
                    ];
                })
                ->filter(fn($cat) => $cat['total_jobs'] > 0 || $cat['total_applicants'] > 0)
                ->sortByDesc('total_jobs')
                ->values();

            return $this->successResponse([
                'data' => $categories,
                'year' => $year,
                'summary' => [
                    'total_categories' => $categories->count(),
                    'total_jobs' => $categories->sum('total_jobs'),
                    'total_applicants' => $categories->sum('total_applicants'),
                    'current_month' => $currentMonth,
                    'last_month' => $lastMonth,
                ]
            ], 'Category analytics fetched successfully!', 200);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to fetch analytics data. ' . $e->getMessage(),
                500
            );
        }
    }


    // Private Funtions ============================================
    private function calculateGrowthRate($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        $growth = (($current - $previous) / $previous) * 100;
        return round($growth, 2);
    }

    private function getPeakMonth($monthlyData)
    {
        $peakValue = max($monthlyData);
        $peakMonth = array_search($peakValue, $monthlyData) + 1;
        return [
            'month' => $peakMonth,
            'value' => $peakValue,
            'month_name' => Carbon::create()->month($peakMonth)->format('F')
        ];
    }

    private function calculateYearlyGrowth($currentYear)
    {
        $previousYear = $currentYear - 1;

        $currentYearApplications = JobApplication::whereYear('created_at', $currentYear)->count();
        $previousYearApplications = JobApplication::whereYear('created_at', $previousYear)->count();

        if ($previousYearApplications === 0) {
            return $currentYearApplications > 0 ? 100 : 0;
        }

        return round((($currentYearApplications - $previousYearApplications) / $previousYearApplications) * 100, 2);
    }

    private function getYearlyStats($year, $employerId = null)
    {
        $applicationsQuery = JobApplication::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->where('type', 'applied')
            ->whereYear('created_at', $year);

        $hiredQuery = JobApplication::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $year)
            ->where('status', 2);

        $vacanciesQuery = JobVacancy::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $year);

        // 🧩 If employer, filter only their data
        if ($employerId) {
            $applicationsQuery->whereHas('jobVacancy', fn($q) => $q->where('employer_id', $employerId));
            $hiredQuery->whereHas('jobVacancy', fn($q) => $q->where('employer_id', $employerId));
            $vacanciesQuery->where('employer_id', $employerId);
        }

        return [
            'applications' => $this->formatMonthlyData($applicationsQuery->groupBy('month')->pluck('count', 'month')->toArray()),
            'hired' => $this->formatMonthlyData($hiredQuery->groupBy('month')->pluck('count', 'month')->toArray()),
            'jobPostings' => $this->formatMonthlyData($vacanciesQuery->groupBy('month')->pluck('count', 'month')->toArray()),
        ];
    }

    private function getYearlySummary($year, $employerId = null)
    {
        $currentYear = Carbon::now()->year;

        // Helper closure for filtering job applications by employer
        $applicationFilter = function ($query) use ($employerId) {
            if ($employerId) {
                $query->whereHas('jobVacancy', fn($q) => $q->where('employer_id', $employerId));
            }
        };

        $newApplications = 0;
        if ($year == $currentYear) {
            $newApplications = JobApplication::whereDate('created_at', Carbon::today())
                ->where($applicationFilter)
                ->where('type', 'applied')
                ->count();
        }

        $rejected = JobApplication::where('status', 3)
            ->whereYear('created_at', $year)
            ->where($applicationFilter)
            ->count();

        $hired = JobApplication::where('status', 2)
            ->whereYear('created_at', $year)
            ->where($applicationFilter)
            ->count();

        $totalApplicants = JobApplication::whereYear('created_at', $year)
            ->where('type', 'applied')
            ->where($applicationFilter)
            ->count();

        return [
            'newApplications' => $newApplications,
            'rejected' => $rejected,
            'hired' => $hired,
            'totalApplicants' => $totalApplicants,
        ];
    }

    private function formatMonthlyData($monthlyData)
    {
        $formatted = array_fill(1, 12, 0);
        foreach ($monthlyData as $month => $count) {
            $formatted[$month] = $count;
        }
        return array_values($formatted);
    }

    public function generateReport(Request $request)
    {
        try {
            $filters = $request->all();
            $user = $request->user();

            // Parse date range
            $dateStart = $dateEnd = null;
            if (!empty($filters['dateRange'])) {
                $dates = explode(' to ', $filters['dateRange']);
                if (count($dates) === 2) {
                    $dateStart = Carbon::parse(trim($dates[0]))->startOfDay();
                    $dateEnd   = Carbon::parse(trim($dates[1]))->endOfDay();
                }
            }

            switch ($filters['reportType'] ?? null) {

                // =================================================================
                // JOBS REPORT (Landscape)
                // =================================================================
                case 'job-vacancies':
                    $query = JobVacancy::with([
                        'category',
                        'jobLocation',
                        'jobType',
                        'jobQualify',
                        'jobLevel',
                        'jobExperience',
                        'employer.user',
                    ]);

                    // Filter by date if provided
                    if (!empty($dateStart) && !empty($dateEnd)) {
                        $query->whereBetween('created_at', [$dateStart, $dateEnd]);
                    }

                    // Filter by employer if provided
                    if (!empty($filters['employer'])) {
                        $query->where('employer_id', $filters['employer']);
                    }

                    $results = $query->latest()->get();

                    return $this->exportPdf(
                        'JOB VACANCIES',
                        $results,
                        $filters,
                        'reports.job_vacancies',
                        'Job_Vacancies_Report',
                        'landscape'
                    );


                    // =================================================================
                    // FM-CDC-CSRPD-07 REPORT (Portrait)
                    // =================================================================
                case 'FM-CDC-CSRPD-07':
                    $query = JobApplication::with([
                        'jobSeeker.user',
                        'jobVacancy',
                    ]);

                    // Filter by date if provided
                    if (!empty($dateStart) && !empty($dateEnd)) {
                        $query->whereBetween('created_at', [$dateStart, $dateEnd]);
                    }

                    // Filter by employer if provided
                    if (!empty($filters['employer'])) {
                        $query->where('employer_id', $filters['employer']);
                    }

                    // Filter by job vacancy if provided
                    if (!empty($filters['job'])) {
                        $query->where('job_vacancy_id', $filters['job']);
                    }

                    // Determine position title for PDF header
                    if (!empty($filters['job'])) {
                        $jobVacancy = JobVacancy::find($filters['job']);
                        $positionTitle = $jobVacancy ?  ['title' => $jobVacancy->title, 'deadline' => $jobVacancy->deadline] : 'No Position';
                    } else {
                        $positionTitle = 'Multiple Positions';
                    }

                    // Get results
                    $results = $query->latest()->get();

                    // Export PDF
                    return $this->exportPdf(
                        $positionTitle,
                        $results,
                        $filters,
                        'reports.applicants-list',
                        'Job_Applicants_Report',
                        'portrait'
                    );

                    // =================================================================
                    // Certification (Referrals) REPORT (Landscape)
                    // =================================================================
                case 'cert-referrals':
                    $query = JobApplication::with([
                        'jobSeeker.user',
                        'jobVacancy',
                        'jobApplicationTransactions',
                    ])->where('status', 1);

                    // Filter by date if provided
                    if (!empty($dateStart) && !empty($dateEnd)) {
                        $query->whereBetween('created_at', [$dateStart, $dateEnd]);
                    }

                    // Filter by employer if provided
                    if (!empty($filters['employer'])) {
                        $query->whereHas('jobVacancy', function ($q) use ($filters) {
                            $q->where('employer_id', $filters['employer']);
                        });
                    }

                    $results = $query->latest()->get();

                    return $this->exportPdf(
                        $request->user()->name,
                        $results,
                        $filters,
                        'reports.job_cert',
                        'Job_Certification_Report',
                        'portrait'
                    );

                case 'FM-CDC-CSRPD-11':
                    $month = $filters['month'] ?? null;
                    $year = $filters['year'] ?? null;

                    $results = ReferenceDetail::with('reference')
                        ->whereHas('reference', function ($q) use ($month, $year, $user) {
                            // Month/year filter on Reference
                            if (!empty($month) && !empty($year)) {
                                $q->where('month', $month)
                                    ->where('year', $year);
                            }

                            // Employer filter
                            if ($user->user_type === 'employer') {
                                $q->where('user_id', $user->id);
                            }
                        })
                        ->latest()
                        ->get();

                    return $this->exportPdf(
                        $request->user()->name,
                        $results,
                        $filters,
                        'reports.employment-11',
                        'Employment_Report',
                        'portrait'
                    );


                case 'FM-CDC-CSRPD-12':
                    $month = $filters['month'] ?? null;
                    $year = $filters['year'] ?? null;

                    $results = ReferenceDetail::with('reference')
                        ->whereHas('reference', function ($q) use ($month, $year, $user) {
                            // Month/year filter on Reference
                            if (!empty($month) && !empty($year)) {
                                $q->where('month', $month)
                                    ->where('year', $year);
                            }

                            // Employer filter
                            if ($user->user_type === 'employer') {
                                $q->where('user_id', $user->id);
                            }
                        })
                        ->latest()
                        ->get();

                    return $this->exportPdf(
                        $request->user()->name,
                        $results,
                        $filters,
                        'reports.employment-12',
                        'Employment_Report',
                        'portrait'
                    );


                case 'FM-CDC-CSRPD-13':
                    $month = $filters['month'] ?? null;
                    $year = $filters['year'] ?? null;

                    $results = ReferenceDetail::with('reference')
                        ->whereHas('reference', function ($q) use ($month, $year, $user) {
                            // Month/year filter on Reference
                            if (!empty($month) && !empty($year)) {
                                $q->where('month', $month)
                                    ->where('year', $year);
                            }

                            // Employer filter
                            if ($user->user_type === 'employer') {
                                $q->where('user_id', $user->id);
                            }
                        })
                        ->latest()
                        ->get();

                    return $this->exportPdf(
                        $request->user()->name,
                        $results,
                        $filters,
                        'reports.employment-13',
                        'Employment_Report',
                        'portrait'
                    );

                case 'reference-data':
                    $month = $filters['month'] ?? null;
                    $year = $filters['year'] ?? null;

                    $results = ReferenceDetail::with('reference')
                        ->whereHas('reference', function ($q) use ($month, $year, $user) {
                            // Month/year filter on Reference
                            if (!empty($month) && !empty($year)) {
                                $q->where('month', $month)
                                    ->where('year', $year);
                            }

                            // Employer filter
                            if ($user->user_type === 'employer') {
                                $q->where('user_id', $user->id);
                            }
                        })
                        ->latest()
                        ->get();

                    return Excel::download(new ReferenceDetailsExport($results), 'Reference_Details.xlsx');



                case 'employment':
                    $month = $filters['month'] ?? null;
                    $year  = $filters['year'] ?? null;

                    $indirectCategories = [
                        'Security',
                        'Janitorial',
                        'Ground',
                        'Construction',
                        'Others'
                    ];

                    $expatNationalities = [
                        'AM',
                        'AUS',
                        'CAN',
                        'BRIT',
                        'IND',
                        'ISR',
                        'JAP',
                        'KOR',
                        'MAL',
                        'RUS',
                        'SING',
                        'TAI',
                        'UKR',
                        'OTHERS'
                    ];

                    $results = Employer::with(['user', 'references' => function ($q) use ($month, $year) {
                        if ($month && $year) {
                            $q->where('month', $month)
                                ->where('year', $year);
                        }
                    }])->get()->map(function ($employer) use ($indirectCategories, $expatNationalities) {

                        $details = $employer->references->pluck('details')->flatten();

                        $indirect = $details->whereIn('category', $indirectCategories)->count();
                        $direct   = $details->whereNotIn('category', $indirectCategories)->count();
                        $expat    = $details->whereIn('nationality', $expatNationalities)->count();

                        $total = $direct + $indirect + $expat;


                        return [
                            'loc_no'   => $employer->locator_number,
                            'company'  => $employer->user->name ?? '',
                            'industry' => $employer->industry,
                            'direct'   => $direct,
                            'indirect' => $indirect,
                            'expat'    => $expat,
                            'total'    => $total,
                            'remarks'  => $total ? '*' : '-',
                        ];
                    });

                    return Excel::download(new ReferenceEmployerExport($results), 'Reference_Employer.xlsx');


                    // =================================================================
                    // DEFAULT
                    // =================================================================
                default:
                    return response()->json([
                        'error'   => 'Invalid report type.',
                        'message' => 'The selected report type is not supported.',
                    ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate report.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportPdf($title, $results, $filters, $view, $filename, $orientation = 'portrait')
    {
        $data = [
            'title'        => $title,
            'generated_at' => now()->format('d F Y'),
            'records'      => $results,
            'filters'      => $filters,
        ];

        $timestamp = now()->format('Ymd_His');

        $pdf = Pdf::loadView($view, $data)->setPaper('a4', $orientation);

        return $pdf->download("{$filename}_{$timestamp}.pdf");
    }
}
