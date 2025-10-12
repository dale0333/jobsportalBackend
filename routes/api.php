<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    UserController,
    ConfigController,
    ConfigDetailController,
    JobEmployeerController,
    JobSeekerController,
    JobApplicationController,
    UserLogController,
    ProfileSettingController
};

use App\Http\Controllers\Setting\{
    EmailController,
    AnnouncementController
};

use App\Http\Controllers\ProfileMenu\{
    MessageController
};


Route::middleware(['system.key', 'throttle:20,1'])->group(function () {
    Route::get('/secure-data', function () {
        return ['message' => 'You passed the system key check!'];
    });

    // Private Request =======================================
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResource('profile-setting', ProfileSettingController::class);
        Route::apiResource('manage-users', UserController::class);

        // Job Configs
        Route::apiResource('job-groups', ConfigController::class);
        Route::apiResource('job-items', ConfigDetailController::class);

        Route::apiResource('job-seekers', JobSeekerController::class);
        Route::apiResource('job-employeers', JobEmployeerController::class);
        Route::apiResource('job-applications', JobApplicationController::class);

        // Settings
        Route::apiResource('setting-email-smtp', EmailController::class);
        Route::apiResource('setting-announcements', AnnouncementController::class);

        // Profile Menus
        Route::apiResource('messages', MessageController::class);
        Route::apiResource('user-logs', UserLogController::class);

        Route::post('change-password', [ProfileSettingController::class, 'changePassword']);
    });

    // Public Reqeusts ======================================================
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('fetch-job-types', [ConfigDetailController::class, 'fetchJobTypes']);
    Route::get('fetch-job-categories', [ConfigDetailController::class, 'fetchJobCategories']);
});
