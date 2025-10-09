<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    UserController,
    ConfigController,
    ConfigDetailController,
    JobEmployeerController,
    JobSeekerController,
    JobApplicationController
};

use App\Http\Controllers\Setting\{
    EmailController,
    AnnouncementController
};

use App\Http\Controllers\ProfileMenu\{
    MessageController
};


Route::middleware(['system.key'])->group(function () {
    // Public API routes
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/secure-data', function () {
        return ['message' => 'You passed the system key check!'];
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });


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

        Route::get('fetch-job-types', [ConfigDetailController::class, 'fetchJobTypes']);
        Route::get('fetch-job-categories', [ConfigDetailController::class, 'fetchJobCategories']);
    });
});
