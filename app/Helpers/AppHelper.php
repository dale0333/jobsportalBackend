<?php

namespace App\Helpers;

use App\Models\{UserLog, EmailSmtp};

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
}
