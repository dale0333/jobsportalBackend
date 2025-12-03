<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class QueueTaskCommand extends Command
{
    protected $signature = 'queue:task';
    protected $description = 'Process up to 10 queued jobs per run';

    public function handle()
    {
        try {
            $this->info("Processing up to 10 jobs from the queue.");

            $exitCode = Artisan::call('queue:work', [
                '--queue' => 'high-priority,default,low-priority',
                '--max-jobs' => 10,
                '--tries' => 3,
                '--stop-when-empty' => true,
            ]);

            if ($exitCode !== 0) {
                $this->error("Queue worker encountered an error. Exit Code: $exitCode");
                Log::error("queue:task - Queue worker failed with exit code $exitCode");
            } else {
                $this->info("Queue processing completed successfully.");
                Log::info("queue:task - Queue processing completed successfully.");
            }
        } catch (\Throwable $e) {
            $this->error("An error occurred: " . $e->getMessage());
            Log::error("queue:task - Error: " . $e->getMessage());
        }
    }
}
