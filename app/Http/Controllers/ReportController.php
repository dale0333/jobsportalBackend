<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User, Reference, JobVacancy, Employer, JobApplication};
use App\Traits\ApiResponseTrait;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\{ReferenceDetailsExport, ReferenceEmployerExport, EmploymentReportExport};

use ZipArchive;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    use ApiResponseTrait;

    public function getEmployers(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', null);

            $query = User::where('user_type', 'employer');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            }

            $data = $query->latest()->paginate($perPage);

            $data = ([
                'items' => $data->items(),
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
            ]);

            return $this->successResponse($data, 'Email SMTP fetch successfully', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to process.', 500, $th->getMessage());
        }
    }

    public function checkEmployerReport(Request $request)
    {
        $filters = $request->all();
        $employerIds = $filters['employer_ids'] ?? [];
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;
        $type = $filters['type'] ?? null;
        $mode = $filters['mode'] ?? null;

        if ($type === 'FM-CDC-CSRPD-07') {

            $jobs = JobVacancy::with([
                'jobApplications.jobSeeker.user',
                'employer.user',
            ])
                ->when($year, fn($q) => $q->whereYear('created_at', $year))
                ->when($month, fn($q) => $q->whereMonth('created_at', $month))
                ->when(
                    $mode !== 'all' && !empty($employerIds),
                    fn($q) => $q->whereHas('employer', fn($q2) => $q2->whereIn('user_id', $employerIds))
                )
                ->latest()
                ->get();

            $employers = $jobs->map(fn($job) => $job->employer->user ?? null)
                ->filter()
                ->unique('id')
                ->values();

            if ($employers->isEmpty()) {
                return response()->json([
                    'error' => 'No employer reports found for the selected report type, month & year.'
                ], 404);
            }

            $ids = $employers->pluck('id');
        } else if ($type === 'cert-referrals') {

            $jobs = JobVacancy::with([
                'jobApplications' => function ($q) {
                    $q->where('status', 'hired')
                        ->with('jobSeeker.user');
                },
                'employer.user',
            ])
                ->when($year, fn($q) => $q->whereYear('created_at', $year))
                ->when($month, fn($q) => $q->whereMonth('created_at', $month))
                ->when(
                    $mode !== 'all' && !empty($employerIds),
                    fn($q) => $q->whereHas('employer', fn($q2) => $q2->whereIn('user_id', $employerIds))
                )
                ->whereHas('jobApplications', fn($q) => $q->where('status', 'hired'))
                ->latest()
                ->get();

            $employers = $jobs->map(fn($job) => $job->employer->user ?? null)
                ->filter()
                ->unique('id')
                ->values();

            if ($employers->isEmpty()) {
                return response()->json([
                    'error' => 'No employer reports found for the selected report type, month & year.'
                ], 404);
            }

            $ids = $employers->pluck('id');
        } else {

            $employersQuery = User::with([
                'reference' => function ($q) use ($month, $year) {
                    $q->where('month', $month)
                        ->where('year', $year)
                        ->with('details');
                }
            ])->where('user_type', 'employer');

            if ($mode !== 'all' && !empty($employerIds)) {
                $employersQuery->whereIn('id', $employerIds);
            }

            $employers = $employersQuery->get();

            if ($employers->isEmpty()) {
                return response()->json(['error' => 'No employers selected.'], 400);
            }

            $employers = $employers->filter(fn($employer) => $employer->reference);

            if ($employers->isEmpty()) {
                return response()->json([
                    'error' => 'No employer reports found for the selected report type, month & year.'
                ], 404);
            }

            $ids = $employers->pluck('id');
        }

        return response()->json(['employer_ids' => $ids]);
    }

    public function generateEmployerReport(Request $request)
    {
        try {

            set_time_limit(300);

            $filters = $request->all();
            $type = $filters['type'] ?? null;

            switch ($type) {
                case 'job_vacancies':
                    return $this->generateJobVacanciesZip($filters);

                case 'cert-referrals':
                    return $this->generateCertZip($filters);

                case 'FM-CDC-CSRPD-07':
                    return $this->generateReferredZip($filters);

                case 'FM-CDC-CSRPD-11':
                case 'FM-CDC-CSRPD-12':
                case 'FM-CDC-CSRPD-13':
                    return $this->generateEmployerPdfReports($filters);

                case 'employment-report':
                    return $this->generateEmploymentReportZip($filters);

                case 'reference-file':
                    return $this->generateReferenceReportZip($filters);

                default:
                    return response()->json([
                        'error' => 'Invalid report type'
                    ], 400);
            }
        } catch (\Throwable $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // private function generateEmployerPdfReports($filters)
    // {
    //     $employerIds = $filters['employer_ids'] ?? [];
    //     $month = $filters['month'] ?? null;
    //     $year = $filters['year'] ?? null;
    //     $type = $filters['type'];

    //     $query = User::with(['reference.details'])->whereIn('id', $employerIds);
    //     $employers = $query->get();

    //     $map = [
    //         'FM-CDC-CSRPD-11' => '11',
    //         'FM-CDC-CSRPD-12' => '12',
    //         'FM-CDC-CSRPD-13' => '13',
    //     ];

    //     $display = $map[$type] ?? '11';

    //     // Ensure ZIP folder exists
    //     $zipDir = storage_path('app/public/zip');
    //     if (!file_exists($zipDir)) {
    //         mkdir($zipDir, 0777, true);
    //     }

    //     $zipFileName = $filters['type'] . '-' . now()->format('Ymd_His') . '.zip';
    //     $zipPath = $zipDir . '/' . $zipFileName;

    //     $zip = new \ZipArchive;
    //     if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
    //         throw new \Exception("Cannot create zip file");
    //     }

    //     foreach ($employers as $employer) {
    //         // Get reference
    //         $reference = $employer->reference()
    //             ->when($month && $year, fn($q) => $q->where('month', $month)->where('year', $year))
    //             ->orderByDesc('year')
    //             ->orderByDesc('month')
    //             ->first();

    //         if (!$reference) continue;

    //         // Load PDF
    //         $pdf = Pdf::loadView("reports.emp-$display", [
    //             'title' => $employer->name,
    //             'generated_at' => now()->format('d F Y'),
    //             'records' => $reference,
    //             'filters' => $filters
    //         ])->setPaper('a4', 'portrait');

    //         $name = preg_replace('/[^A-Za-z0-9\-]/', '-', $employer->name);
    //         $empId = str_pad($employer->id, 6, '0', STR_PAD_LEFT);

    //         $filename = "{$empId}-{$name}";
    //         if ($month && $year) {
    //             $filename .= "-{$year}_{$month}";
    //         }

    //         $zip->addFromString("{$filename}.pdf", $pdf->output());
    //     }

    //     $zip->close();

    //     return response()->json([
    //         'download' => asset("storage/zip/$zipFileName")
    //     ]);
    // }

    private function generateEmployerPdfReports($filters)
    {
        $employerIds = $filters['employer_ids'] ?? [];
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;
        $type = $filters['type'];

        $map = [
            'FM-CDC-CSRPD-11' => '11',
            'FM-CDC-CSRPD-12' => '12',
            'FM-CDC-CSRPD-13' => '13',
        ];

        $display = $map[$type] ?? '11';

        /**
         * ✅ Ensure ZIP folder exists
         */
        $zipDir = storage_path('app/public/zip');
        if (!file_exists($zipDir)) {
            mkdir($zipDir, 0777, true);
        }

        $zipFileName = $filters['type'] . '-' . now()->format('Ymd_His') . '.zip';
        $zipPath = $zipDir . '/' . $zipFileName;

        $zip = new \ZipArchive;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create zip file");
        }

        /**
         * ✅ Loop employers
         */
        foreach ($employerIds as $empIdRaw) {

            $employer = User::with(['reference.details'])->find($empIdRaw);
            if (!$employer) continue;

            $reference = $employer->reference()
                ->when(
                    $month && $year,
                    fn($q) =>
                    $q->where('month', $month)->where('year', $year)
                )
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->first();

            if (!$reference) continue;

            $rowCount = $reference->details->count();

            /**
             * ✅ Clean filename
             */
            $name = preg_replace('/[^A-Za-z0-9\-]/', '-', $employer->name);
            $empId = str_pad($employer->id, 6, '0', STR_PAD_LEFT);

            $filename = "{$empId}-{$name}";
            if ($month && $year) {
                $filename .= "-{$year}_{$month}";
            }

            /**
             * 🔥 CONDITION: ONLY FOR TYPE 13
             */
            if ($type === 'FM-CDC-CSRPD-13' && $rowCount > 2000) {

                /**
                 * ✅ Generate Excel instead
                 */
                $excelData = Excel::raw(
                    new EmploymentReportExport($reference, $filters),
                    \Maatwebsite\Excel\Excel::XLSX
                );

                $zip->addFromString("{$filename}.xlsx", $excelData);
            } else {

                /**
                 * ✅ Generate PDF
                 */
                $pdf = Pdf::loadView("reports.emp-$display", [
                    'title' => $employer->name,
                    'generated_at' => now()->format('d F Y'),
                    'records' => $reference,
                    'filters' => $filters
                ])->setPaper('a4', 'portrait');

                $zip->addFromString("{$filename}.pdf", $pdf->output());

                unset($pdf);
            }
        }

        $zip->close();

        return response()->json([
            'download' => asset("storage/zip/$zipFileName")
        ]);
    }

    private function generateJobVacanciesZip($filters)
    {
        $employerIds = $filters['employer_ids'] ?? [];
        $month = (int) ($filters['month'] ?? 0);
        $year = (int) ($filters['year'] ?? 0);
        $mode = $filters['mode'] ?? null;

        $query = JobVacancy::with([
            'category',
            'jobLocation',
            'jobType',
            'jobQualify',
            'jobLevel',
            'jobExperience',
            'employer.user'
        ])
            ->when($year, fn($q) => $q->whereYear('created_at', $year))
            ->when($month, fn($q) => $q->whereMonth('created_at', $month))
            ->latest();

        if ($mode !== 'all' && !empty($employerIds)) {
            $query->whereHas('employer', fn($q) => $q->whereIn('user_id', $employerIds));
        }

        $results = $query->get();

        $pdf = Pdf::loadView("reports.job-vacant", [
            'title' => 'JOB VACANCIES',
            'generated_at' => now()->format('d F Y'),
            'records' => $results,
            'filters' => $filters
        ])->setPaper('a4', 'landscape');

        $zipDir = storage_path('app/public/zip');
        if (!file_exists($zipDir)) {
            mkdir($zipDir, 0777, true);
        }

        $zipFileName = $filters['type'] . '-' . now()->format('Ymd_His') . '.zip';
        $zipPath = $zipDir . '/' . $zipFileName;

        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $zip->addFromString('Job_Vacancies.pdf', $pdf->output());

        $zip->close();

        return response()->json([
            'download' => asset("storage/zip/$zipFileName")
        ]);
    }

    private function generateReferredZip($filters)
    {
        $employerIds = $filters['employer_ids'] ?? [];
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;

        $jobs = JobVacancy::with([
            'jobApplications.jobSeeker.user',
            'employer.user',
        ])
            ->when($year, fn($q) => $q->whereYear('created_at', $year))
            ->when($month, fn($q) => $q->whereMonth('created_at', $month))
            ->when(!empty($employerIds), function ($q) use ($employerIds) {
                $q->whereHas('employer', function ($q2) use ($employerIds) {
                    $q2->whereIn('user_id', $employerIds);
                });
            })
            ->latest()
            ->get()
            ->groupBy(fn($job) => $job->employer->user->id ?? null);

        $zipDir = storage_path('app/public/zip');

        if (!file_exists($zipDir)) {
            mkdir($zipDir, 0777, true);
        }

        $zipFileName = $filters['type'] . '-' . now()->format('Ymd_His') . '.zip';
        $zipPath = $zipDir . '/' . $zipFileName;

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create zip file");
        }

        foreach ($jobs as $employerId => $employerJobs) {

            $employer = $employerJobs->first()->employer->user ?? null;
            $job = $employerJobs->first() ?? null;
            if (!$employer) continue;

            // Pass all jobs for this employer to the PDF
            $pdf = Pdf::loadView("reports.emp-07", [
                'job' => $job ?? '-',
                'generated_at' => now()->format('d F Y'),
                'records' => $employerJobs,
                'filters' => $filters
            ])->setPaper('a4', 'portrait');

            $name = preg_replace('/[^A-Za-z0-9\-]/', '-', $employer->name);
            $empId = str_pad($employer->id, 6, '0', STR_PAD_LEFT);

            $zip->addFromString(
                "{$empId}-{$name}.pdf",
                $pdf->output()
            );
        }

        $zip->close();

        return response()->json([
            'download' => asset("storage/zip/$zipFileName")
        ]);
    }

    private function generateCertZip($filters)
    {
        $employerIds = $filters['employer_ids'] ?? [];
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;

        $jobs = JobVacancy::with([
            'jobApplications' => function ($q) {
                $q->where('status', 'hired')
                    ->with('jobSeeker.user');
            },
            'employer.user',
        ])
            ->when($year, fn($q) => $q->whereYear('created_at', $year))
            ->when($month, fn($q) => $q->whereMonth('created_at', $month))
            ->when(!empty($employerIds), function ($q) use ($employerIds) {
                $q->whereHas('employer', function ($q2) use ($employerIds) {
                    $q2->whereIn('user_id', $employerIds);
                });
            })
            ->whereHas('jobApplications', fn($q) => $q->where('status', 'hired'))
            ->latest()
            ->get()
            ->groupBy(fn($job) => $job->employer->user->id ?? null);

        $zipDir = storage_path('app/public/zip');

        if (!file_exists($zipDir)) {
            mkdir($zipDir, 0777, true);
        }

        $zipFileName = $filters['type'] . '-' . now()->format('Ymd_His') . '.zip';
        $zipPath = $zipDir . '/' . $zipFileName;

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create zip file");
        }

        foreach ($jobs as $employerId => $employerJobs) {

            $employer = $employerJobs->first()->employer->user ?? null;
            if (!$employer) continue;

            // collect all hired applications from employer jobs
            $records = $employerJobs
                ->flatMap(function ($job) {
                    return $job->jobApplications;
                })
                ->values();

            $job = $employerJobs->first();

            $pdf = Pdf::loadView("reports.cert", [
                'job' => $job,
                'generated_at' => now()->format('F Y'),
                'records' => $records,
                'filters' => $filters
            ])->setPaper('a4', 'portrait');

            $name = preg_replace('/[^A-Za-z0-9\-]/', '-', $employer->name);
            $empId = str_pad($employer->id, 6, '0', STR_PAD_LEFT);

            $zip->addFromString(
                "{$empId}-{$name}.pdf",
                $pdf->output()
            );
        }

        $zip->close();

        return response()->json([
            'download' => asset("storage/zip/$zipFileName")
        ]);
    }

    private function generateReferenceReportZip($filters)
    {
        $employerIds = $filters['employer_ids'] ?? [];
        $month = (int)($filters['month'] ?? 0);
        $year = (int)($filters['year'] ?? 0);
        $mode = $filters['mode'] ?? null;

        /**
         * ✅ STEP 1: Build query
         */
        $query = Reference::with('details')
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($year, fn($q) => $q->where('year', $year))
            ->when(
                !empty($employerIds) && $mode !== 'all',
                fn($q) => $q->whereIn('user_id', $employerIds)
            );

        $references = $query->get();

        /**
         * ❌ No data
         */
        if ($references->isEmpty()) {
            return response()->json([
                'error' => 'No employer reports found for the selected report type, month & year.'
            ], 404);
        }

        /**
         * ✅ STEP 2: Flatten all details
         */
        $allDetails = $references->pluck('details')->flatten();

        if ($allDetails->isEmpty()) {
            return response()->json([
                'error' => 'No reference details found.'
            ], 404);
        }

        /**
         * ✅ STEP 3: Generate Excel (memory)
         */
        $excelData = Excel::raw(
            new ReferenceDetailsExport($allDetails),
            \Maatwebsite\Excel\Excel::XLSX
        );

        /**
         * ✅ STEP 4: Ensure directory exists
         */
        $dir = storage_path('app/public/reports');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        /**
         * ✅ STEP 5: Save Excel file
         */
        $fileName = ($filters['type'] ?? 'reference') . '-' . now()->format('Ymd_His') . '.xlsx';
        $filePath = $dir . '/' . $fileName;

        file_put_contents($filePath, $excelData);

        /**
         * ✅ STEP 6: Return download link
         */
        return response()->json([
            'download' => asset("storage/reports/$fileName")
        ]);
    }

    private function generateEmploymentReportZip($filters)
    {
        $employerIds = $filters['employer_ids'] ?? [];
        $month = (int)($filters['month'] ?? 0);
        $year = (int)($filters['year'] ?? 0);
        $mode = $filters['mode'] ?? null;

        $indirectCategories = ['Security', 'Janitorial', 'Ground', 'Construction', 'Others'];

        $expatNationalities = [
            'American',
            'Australian',
            'British',
            'Canadian',
            'Chinese',
            'Indian',
            'Israeli',
            'Japanese',
            'Korean',
            'Malaysian',
            'Russian',
            'Singaporean',
            'Taiwanese',
            'Ukrainian',
            'Others'
        ];

        /**
         * ✅ STEP 1: Get latest month/year if none selected
         */
        if (!$month || !$year) {
            $latest = \App\Models\Reference::orderByDesc('year')
                ->orderByDesc('month')
                ->first();

            if ($latest) {
                $month = $latest->month;
                $year = $latest->year;
            }
        }

        /**
         * ❌ If still no data
         */
        if (!$month || !$year) {
            return response()->json([
                'error' => 'No available reference data.'
            ], 404);
        }

        /**
         * ✅ STEP 2: Build query
         */
        $query = Employer::with(['user', 'references.details']);

        if ($mode !== 'all' && !empty($employerIds)) {
            $query->whereIn('user_id', $employerIds);
        }

        /**
         * ✅ STEP 3: Transform data
         */
        $results = $query->get()->map(function ($employer) use (
            $month,
            $year,
            $indirectCategories,
            $expatNationalities,
        ) {

            $currentRefs = $employer->references
                ->where('month', $month)
                ->where('year', $year);

            if ($currentRefs->isNotEmpty()) {
                $details = $currentRefs->pluck('details')->flatten();
                $remarks = '*';
                $usedMonth = $month;
                $usedYear = $year;
            } else {
                $latestRef = $employer->references()
                    ->orderByDesc('year')
                    ->orderByDesc('month')
                    ->first();

                if ($latestRef) {
                    $details = collect($latestRef->details);
                    $remarks = '**';
                    $usedMonth = $latestRef->month;
                    $usedYear = $latestRef->year;
                } else {
                    $details = collect();
                    $remarks = '^';
                    $usedMonth = null;
                    $usedYear = null;
                }
            }

            $indirect = $details->whereIn('category', $indirectCategories)->count();
            $direct   = $details->whereNotIn('category', $indirectCategories)->count();
            $expat    = $details->whereIn('nationality', $expatNationalities)->count();

            return [
                'loc_no'   => $employer->locator_number,
                'company'  => $employer->user->name ?? '',
                'industry' => $employer->industry,
                'direct'   => $direct,
                'indirect' => $indirect,
                'expat'    => $expat,
                'total'    => $direct + $indirect + $expat,
                'remarks'  => $remarks,
                'month_used' => $usedMonth,
                'year_used'  => $usedYear,
            ];
        });

        /**
         * ❌ If empty results
         */
        if ($results->isEmpty()) {
            return response()->json([
                'error' => 'No employer reports found.'
            ], 404);
        }

        /**
         * ✅ STEP 4: Generate Excel (in memory)
         */
        $excelData = Excel::raw(
            new ReferenceEmployerExport($results),
            \Maatwebsite\Excel\Excel::XLSX
        );

        /**
         * ✅ STEP 5: Ensure directory exists
         */
        $dir = storage_path('app/public/reports');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        /**
         * ✅ STEP 6: Save Excel file
         */
        $fileName = ($filters['type'] ?? 'employment') .
            "-{$year}_{$month}_" . now()->format('Ymd_His') . '.xlsx';

        $filePath = $dir . '/' . $fileName;

        file_put_contents($filePath, $excelData);

        /**
         * ✅ STEP 7: Return direct download link
         */
        return response()->json([
            'download' => asset("storage/reports/$fileName"),
            'month' => $month,
            'year' => $year
        ]);
    }

    // sigle report
    public function generateEmpSingleReports(Request $request)
    {
        $employerId = $request->id;
        $month = $request->month;
        $year = $request->year;
        $type = $request->type;

        $employer = User::with('reference.details')->find($employerId);
        if (!$employer) {
            return response()->json(['error' => 'Employer not found'], 404);
        }

        $map = [
            'FM-CDC-CSRPD-11' => '11',
            'FM-CDC-CSRPD-12' => '12',
            'FM-CDC-CSRPD-13' => '13',
        ];

        $display = $map[$type] ?? '11';

        $reference = $employer->reference()
            ->when($month && $year, fn($q) => $q->where('month', $month)->where('year', $year))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        if (!$reference) {
            return response()->json(['error' => 'No reference found for selected month/year'], 404);
        }

        $rowCount = $reference->details->count();
        $filters = $request->all();

        if ($type === 'FM-CDC-CSRPD-13' && $rowCount > 2000) {
            $fileName = 'Employment_Report_' . preg_replace('/[^A-Za-z0-9\-]/', '-', $employer->name) . "_{$month}_{$year}.xlsx";
            return Excel::download(
                new \App\Exports\EmploymentReportExport($reference, $filters),
                $fileName,
                \Maatwebsite\Excel\Excel::XLSX
            );
        }

        $pdf = Pdf::loadView("reports.emp-$display", [
            'title' => $employer->name,
            'generated_at' => now()->format('d F Y'),
            'records' => $reference,
            'filters' => $filters
        ])->setPaper('a4', 'portrait');

        $fileName = preg_replace('/[^A-Za-z0-9\-]/', '-', $employer->name) . "_{$month}_{$year}.pdf";
        return $pdf->stream($fileName, ['Content-Type' => 'application/pdf']);
    }

    public function generateJobHired(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search  = $request->input('search');

            $query = JobApplication::with([
                'jobSeeker.user.jobSeeker',
                'jobVacancy.employer.user'
            ])->where('status', 2);

            // Search by job title OR job seeker name
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhereHas('jobSeeker.user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $data = $query->latest()->paginate($perPage);

            // Wrap pagination data
            $result = [
                'items'        => $data->items(),
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
            ];

            return $this->successResponse($result, 'Fetched hired jobs successfully.', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to fetch hired jobs.', 500, $th->getMessage());
        }
    }

    public function checkEmployerInprogress(Request $request)
    {
        $user = $request->user();

        try {
            $query = JobApplication::with([
                'jobSeeker.user.jobSeeker',
                'jobVacancy.employer.user'
            ])
                ->whereIn('status', [0, 1]) // interview / process
                ->where('updated_at', '<=', Carbon::now()->subDays(5))
                ->where('status', 'applied')
                ->whereHas('jobVacancy.employer', function ($q) use ($user) {
                    $q->where('id', $user->employer->id);
                });

            $data = $query->latest()->get();

            return $this->successResponse($data, 'Fetched in-progress applications (5 days no update).', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to fetch in-progress applications.', 500, $th->getMessage());
        }
    }
}
