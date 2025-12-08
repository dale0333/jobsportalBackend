<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendPostDeadlineNotification;
use App\Models\JobVacancy;
use Carbon\Carbon;

class QueuePostDeadline extends Command
{
    protected $signature = 'queue:deadline';
    protected $description = 'Post Deadline Reminders to Employer';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow();
        $vacancies = JobVacancy::with(['employer.user'])
            ->whereDate('deadline', '=', $tomorrow->toDateString())
            ->get();

        try {
            foreach ($vacancies as $vacancy) {
                $data = [
                    'employer_email' => $vacancy->employer->user->email,
                    'employer_name' => $vacancy->employer->user->name,
                    'vacancy_title' => $vacancy->title,
                    'deadline' => $vacancy->deadline->toDateString(),
                    'vacancy_id' => $vacancy->id,
                ];

                SendPostDeadlineNotification::dispatch($data)->onQueue('low-priority');
            }

            $this->info("Post deadline notifications have been queued for " . $vacancies->count() . " vacancies.");
            Log::info("queue:deadline - Post deadline notifications queued for " . $vacancies->count() . " vacancies.");
        } catch (\Throwable $e) {
            $this->error("An error occurred: " . $e->getMessage());
            Log::error("queue:deadline - Error: " . $e->getMessage());
        }
    }
}
