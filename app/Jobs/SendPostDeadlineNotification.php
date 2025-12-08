<?php

namespace App\Jobs;

use App\Helpers\AppHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPostDeadlineNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        AppHelper::mailerConfig();

        $emailData = [
            'title' => 'Job Deadline Passed - Action Required',
            'employer_name' => $this->data['employer_name'],
            'vacancy_title' => $this->data['vacancy_title'],
            'deadline_date' => $this->data['deadline'],
            'dashboard_url' => AppHelper::backEndUrl('login'),
            'app_name' => config('app.name'),
            'current_year' => date('Y'),
            'support_email' => config('mail.support_email', 'support@example.com'),
        ];

        Mail::send('emails.post-deadline', $emailData, function ($message) {
            $message->to($this->data['employer_email'])
                ->subject('Action Required: Job Deadline Passed - ' . $this->data['vacancy_title']);
        });
    }
}
