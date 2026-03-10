<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use ZipArchive;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\{User, ReferenceDetail};

class GenerateEmployerReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $filters;
    protected $jobId;

    public function __construct($userId, $filters, $jobId)
    {
        $this->userId = $userId;
        $this->filters = $filters;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        try {
            $employerIds = $this->filters['employer_ids'] ?? [];
            $month = $this->filters['month'] ?? null;
            $year = $this->filters['year'] ?? null;

            Cache::put("report_progress_{$this->userId}_{$this->jobId}", 1, 3600);

            $employers = User::whereIn('id', $employerIds)->get();

            $zipFileName = 'Employer_Report_' . now()->format('Ymd_His') . '.zip';
            $zipPath = storage_path("app/public/$zipFileName");

            $zip = new ZipArchive;

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

                $total = $employers->count();
                $count = 0;

                foreach ($employers as $employer) {
                    $results = ReferenceDetail::with('reference')
                        ->whereHas('reference', function ($q) use ($month, $year, $employer) {
                            if (!empty($month) && !empty($year)) {
                                $q->where('month', $month)->where('year', $year);
                            }
                            $q->where('user_id', $employer->id);
                        })
                        ->latest()
                        ->get();

                    $data = [
                        'title' => $employer->name,
                        'generated_at' => now()->format('d F Y'),
                        'records' => $results,
                        'filters' => []
                    ];

                    $pdf = Pdf::loadView('reports.employment-11', $data)
                        ->setPaper('a4', 'portrait');

                    $zip->addFromString("Employer_{$employer->id}.pdf", $pdf->output());

                    $count++;
                    $progress = round(($count / $total) * 100);
                    Cache::put("report_progress_{$this->userId}_{$this->jobId}", $progress, 3600);
                }

                $zip->close();

                // Store the download URL
                Cache::put("report_download_{$this->userId}_{$this->jobId}", asset("storage/$zipFileName"), 3600);
                Cache::put("report_progress_{$this->userId}_{$this->jobId}", 100, 3600);
            }
        } catch (\Exception $e) {
            Cache::put("report_error_{$this->userId}_{$this->jobId}", $e->getMessage(), 3600);
            Cache::put("report_progress_{$this->userId}_{$this->jobId}", -1, 3600);
        }
    }
}
