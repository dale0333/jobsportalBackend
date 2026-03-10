<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User, Reference, JobVacancy, Employer};
use App\Traits\ApiResponseTrait;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\{ReferenceDetailsExport, ReferenceEmployerExport};

use ZipArchive;
use Barryvdh\DomPDF\Facade\Pdf;

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

    private function generateEmployerPdfReports($filters)
    {
        $employerIds = $filters['employer_ids'] ?? [];
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;
        $type = $filters['type'];

        $query = User::with([
            'reference' => function ($q) use ($month, $year) {
                $q->where('month', $month)
                    ->where('year', $year)
                    ->with('details');
            }
        ])->where('user_type', 'employer');

        if (!empty($employerIds)) {
            $query->whereIn('id', $employerIds);
        }

        $employers = $query->get();

        $map = [
            'FM-CDC-CSRPD-11' => '11',
            'FM-CDC-CSRPD-12' => '12',
            'FM-CDC-CSRPD-13' => '13',
        ];

        $display = $map[$type] ?? '11';

        $zipDir = storage_path('app/public/zip');
        if (!file_exists($zipDir)) {
            mkdir($zipDir, 0777, true);
        }

        $zipFileName = 'report-' . now()->format('Ymd_His') . '.zip';
        $zipPath = $zipDir . '/' . $zipFileName;

        $zip = new \ZipArchive;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create zip file");
        }

        foreach ($employers as $employer) {

            $reference = $employer->reference->first();
            if (!$reference) continue;

            $pdf = Pdf::loadView("reports.emp-$display", [
                'title' => $employer->name,
                'generated_at' => now()->format('d F Y'),
                'records' => $reference,
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

    private function generateEmploymentReportZip($filters)
    {
        $employerIds = $filters['employer_ids'] ?? [];
        $month = (int)($filters['month'] ?? 0);
        $year = (int)($filters['year'] ?? 0);
        $mode = $filters['mode'] ?? null;

        $previousMonth = $month == 1 ? 12 : $month - 1;
        $previousYear = $month == 1 ? $year - 1 : $year;

        $indirectCategories = ['Security', 'Janitorial', 'Ground', 'Construction', 'Others'];

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

        $query = Employer::with(['user', 'references.details']);

        if ($mode !== 'all' && !empty($employerIds)) {
            $query->whereIn('user_id', $employerIds);
        }

        $results = $query->get()->map(function ($employer) use (
            $month,
            $year,
            $previousMonth,
            $previousYear,
            $expatNationalities,
            $indirectCategories,
        ) {

            $currentRefs = $employer->references
                ->where('month', $month)
                ->where('year', $year);

            $previousRefs = $employer->references
                ->where('month', $previousMonth)
                ->where('year', $previousYear);

            if ($currentRefs->isNotEmpty()) {
                $details = $currentRefs->pluck('details')->flatten();
                $remarks = '*';
            } elseif ($previousRefs->isNotEmpty()) {
                $details = $previousRefs->pluck('details')->flatten();
                $remarks = '**';
            } else {
                $details = collect();
                $remarks = '^';
            }

            $indirect = $details->whereIn('category', $indirectCategories)->count();
            $direct   = $details->whereNotIn('category', $indirectCategories)->count();
            $expat    = $details->whereIn('nationality', $expatNationalities)->count();

            return [
                'loc_no' => $employer->locator_number,
                'company' => $employer->user->name ?? '',
                'industry' => $employer->industry,
                'direct' => $direct,
                'indirect' => $indirect,
                'expat' => $expat,
                'total' => $direct + $indirect + $expat,
                'remarks' => $remarks
            ];
        });

        // Generate Excel **in memory**
        $excelData = Excel::raw(new ReferenceEmployerExport($results), \Maatwebsite\Excel\Excel::XLSX);

        // Ensure ZIP directory exists
        $zipDir = storage_path('app/public/zip');
        if (!file_exists($zipDir)) {
            mkdir($zipDir, 0777, true);
        }

        // Create ZIP file
        $zipFileName = 'report-' . now()->format('Ymd_His') . '.zip';
        $zipPath = $zipDir . '/' . $zipFileName;

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            throw new \Exception("Cannot create ZIP file");
        }

        // Add Excel data directly to ZIP
        $zip->addFromString('Employment_Report.xlsx', $excelData);
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

        $zipFileName = 'Job_Vacancies_' . now()->format('Ymd_His') . '.zip';
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

        $zipFileName = 'referred-' . now()->format('Ymd_His') . '.zip';
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

        $zipFileName = 'certificate-' . now()->format('Ymd_His') . '.zip';
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

        // Base query
        $query = Reference::with('details')
            ->when($month, fn($q) => $q->where('month', $month))
            ->when($year, fn($q) => $q->where('year', $year))
            ->when(!empty($employerIds) && $mode !== 'all', fn($q) => $q->whereIn('user_id', $employerIds));

        $references = $query->get();

        if ($references->isEmpty()) {
            return response()->json([
                'error' => 'No employer reports found for the selected report type, month & year.'
            ], 404);
        }

        // Flatten all details for export
        $allDetails = $references->pluck('details')->flatten();

        // Generate Excel in memory
        $excelData = Excel::raw(new ReferenceDetailsExport($allDetails), \Maatwebsite\Excel\Excel::XLSX);

        // Ensure ZIP directory exists
        $zipDir = storage_path('app/public/zip');
        if (!file_exists($zipDir)) {
            mkdir($zipDir, 0777, true);
        }

        // Create ZIP file
        $zipFileName = 'reference-file-' . now()->format('Ymd_His') . '.zip';
        $zipPath = $zipDir . '/' . $zipFileName;

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            throw new \Exception("Cannot create ZIP file");
        }

        // Add Excel data to ZIP
        $zip->addFromString('Employment_Report.xlsx', $excelData);
        $zip->close();

        return response()->json([
            'download' => asset("storage/zip/$zipFileName")
        ]);
    }
}
