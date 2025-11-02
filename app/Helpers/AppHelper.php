<?php

namespace App\Helpers;

use App\Models\{UserLog, EmailSmtp, SubCategory, Notification};
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class AppHelper
{
    public static function userLog($userId, $userAction)
    {
        UserLog::create([
            'user_id' => $userId,
            'action' => $userAction
        ]);
    }

    public static function mailerConfig()
    {
        $data = EmailSmtp::where('is_active', 1)->first();

        if (!$data) {
            throw new \Exception('No active SMTP configuration found.');
        }

        $config = [
            'transport' => 'smtp',
            'host' => $data->host,
            'port' => $data->port,
            'encryption' => $data->encryption,
            'username' => $data->email,
            'password' => $data->password,
            'timeout' => null,
        ];

        // Update the configuration
        config(['mail.mailers.smtp' => $config]);
        config(['mail.from.address' => $data->email]);
        config(['mail.from.name' => config('app.name')]);

        app()->forgetInstance('mailer');
        app()->forgetInstance('mail.manager');
        Mail::flushMacros();
    }

    public static function getSubCategoryNames($jobSubCategory)
    {
        if (empty($jobSubCategory)) {
            return [];
        }

        // Handle JSON or array input
        if (is_string($jobSubCategory)) {
            $jobSubCategory = json_decode($jobSubCategory, true);
        }

        if (!is_array($jobSubCategory)) {
            return [];
        }

        // Fetch sub-category names
        return SubCategory::whereIn('id', $jobSubCategory)
            ->pluck('name')
            ->toArray();
    }

    public static function sendVerificationEmail($user)
    {
        try {
            AppHelper::mailerConfig();

            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addHours(24),
                [
                    'id' => $user->getKey(),
                    'hash' => sha1($user->getEmailForVerification()),
                ]
            );

            $emailData = [
                'title' => 'Verify Your Email Address - CDC Job Portal',
                'user' => $user,
                'verificationUrl' => $verificationUrl,
                'appName' => 'CDC Job Portal',
                'currentYear' => date('Y'),
            ];

            // Send the email
            Mail::send('emails.verify-email', $emailData, function ($message) use ($user, $emailData) {
                $message->to($user->email)
                    ->subject($emailData['title']);
            });
        } catch (\Throwable $e) {
            Log::error('Email verification failed: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'email' => $user->email ?? null,
            ]);
        }
    }

    public static function backEndUrl($path = '')
    {
        $baseUrl = config('app.env') === 'local'
            ? 'http://localhost:8080'
            : 'https://cdc-jobsportal.itwattsavers.com';

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    public static function storedNotification($user, $type, $title, $message, array $data = [])
    {
        try {
            // Create notification record
            Notification::create([
                'user_id' => $user->id,
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'data'    => !empty($data) ? json_encode($data) : null,
                'is_read' => false,
            ]);

            Log::info("Notification has been stored.");
        } catch (\Exception $e) {
            Log::error("Failed to save notification" . $e->getMessage());
        }
    }

    public static function sendNotificationEmail($user, $type, $title, $message, array $data = [])
    {
        if (!$user) {
            Log::warning('sendNotificationEmail: User is null');
            return;
        }

        if (!$user->email) {
            Log::warning("sendNotificationEmail: User {$user->id} has no email address");
            return;
        }

        self::mailerConfig();

        try {
            // Create notification record
            Notification::create([
                'user_id' => $user->id,
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'data'    => !empty($data) ? json_encode($data) : null,
                'is_read' => false,
            ]);

            $emailData = [
                'title'     => $title,
                'content'   => $message,
                'type'      => $type,
                'userName'  => $user->name,
                'timestamp' => now()->format('F j, Y \a\t g:i A'),
                'appName' => config('app.name'),
                'supportEmail' => config('app.support_email'),
                'data'      => $data,
            ];

            if ($user->is_email) {
                Mail::send('emails.generic-notification', $emailData, function ($message) use ($user, $title) {
                    $message->to($user->email)
                        ->subject($title);
                });

                Log::info("Notification email sent successfully to {$user->email}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification email to {$user->email}: " . $e->getMessage());
        }
    }
}
