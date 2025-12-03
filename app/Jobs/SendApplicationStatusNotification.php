<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Helpers\AppHelper;

class SendApplicationStatusNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $type;
    public $title;
    public $message;
    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $type, string $title, string $message, array $data = [])
    {
        $this->user = $user;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            AppHelper::mailerConfig();

            // Prepare email data for the template
            $emailData = [
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'title' => $this->title,
                'type' => $this->type,
                'content' => $this->message,
                'data' => $this->data,
                'timestamp' => now()->format('F j, Y \a\t g:i A'),
                'supportEmail' => config('mail.support_email', 'support@example.com'),
            ];

            // Send email using the dynamic template
            Mail::send('emails.application-status-updated', $emailData, function ($message) use ($emailData) {
                $message->to($emailData['userEmail'])
                    ->subject($emailData['title']);
            });

            Log::info("Application status notification sent to: {$this->user->email} with title: {$this->title}");
        } catch (\Exception $e) {
            Log::error("Failed to send application status notification: " . $e->getMessage());
        }
    }
}
