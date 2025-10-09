<?php

namespace App\Helpers;

use App\Models\{UserLog, EmailSmtp, SubCategory};

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

        config(['mail.mailers.smtp' => $config]);
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
}
