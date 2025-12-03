<?php

namespace App\Jobs;

use App\Helpers\AppHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewAccountEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $password;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        AppHelper::mailerConfig();

        $emailData = [
            'title' => 'Account Created Successful - ' . config('app.name'),
            'user' => $this->user,
            'texPast' => $this->password,
            'appName' => config('app.name'),
            'currentYear' => date('Y'),
            'supportEmail' => config('app.support_email'),
            'loginUrl' => AppHelper::backEndUrl('login'),
        ];

        Mail::send('emails.new-account', $emailData, function ($message) use ($emailData) {
            $message->to($this->user->email)
                ->subject($emailData['title']);
        });
    }
}
