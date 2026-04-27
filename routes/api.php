<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\EmailVerificationController;

use App\Http\Controllers\{
    AuthController,
    UserController,
    FetchServiceController,
    JobVacancyController,
    JobSeekerController,
    JobApplicationController,
    SeekerAppController,
    SeekerDocumentController,
    UserLogController,
    ProfileSettingController,
    DashboardController,
    ReferenceController,
    ReferenceDetailController,
    NotificationController,
    ReportController
};

use App\Http\Controllers\PesoSchool\{
    BulkUploadController,
    ExcelUploadController
};

use App\Http\Controllers\Setting\{
    EmailController,
    AnnouncementController,
    SubAttributeController,
    AttributeController,
    CategoryController,
    SubCategoryController,
    ContactController,
};

use App\Http\Controllers\ProfileMenu\{
    MessageController
};

Route::middleware(['system.key', 'throttle:100,1'])->group(function () {
    Route::get('/secure-data', function () {
        return ['message' => 'You passed the system key check!'];
    });

    // Private Request =======================================
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/analytics/statics-one', [DashboardController::class, 'getAnalytic1']);
        Route::get('/analytics/statics-two', [DashboardController::class, 'getAnalytic2']);
        Route::get('/analytics/statics-three', [DashboardController::class, 'getAnalytic3']);
        Route::get('/analytics/statics-four', [DashboardController::class, 'getAnalytic4']);

        Route::get('/analytics/available-years', [DashboardController::class, 'getAvailableYears']);
        Route::get('/analytics/job-widgets', [DashboardController::class, 'getJobWidgets']);
        Route::post('/generate/reports', [DashboardController::class, 'generateReport']);

        // Notifications ===================================================
        Route::apiResource('notifications', NotificationController::class);
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

        Route::apiResource('profile-setting', ProfileSettingController::class);
        Route::apiResource('manage-users', UserController::class);

        Route::apiResource('job-seekers', JobSeekerController::class);
        Route::apiResource('job-vacancies', JobVacancyController::class);
        Route::apiResource('job-applications', JobApplicationController::class);

        Route::apiResource('seeker-applications', SeekerAppController::class);
        Route::apiResource('seeker-documents', SeekerDocumentController::class);

        Route::apiResource('user-references', ReferenceController::class);
        Route::apiResource('references-details', ReferenceDetailController::class);

        // Settings
        Route::apiResource('setting-email-smtp', EmailController::class);
        Route::apiResource('setting-announcements', AnnouncementController::class);
        Route::apiResource('setting-attributes', AttributeController::class);
        Route::apiResource('setting-sub-attributes', SubAttributeController::class);
        Route::apiResource('setting-categories', CategoryController::class);
        Route::apiResource('setting-sub-categories', SubCategoryController::class);

        // Profile Menus
        Route::apiResource('messages', MessageController::class);
        Route::apiResource('user-logs', UserLogController::class);

        // Peso School Menus
        Route::apiResource('bulk-uploads', BulkUploadController::class);
        Route::apiResource('excel-uploads', ExcelUploadController::class);

        Route::post('change-password', [ProfileSettingController::class, 'changePassword']);
        Route::post('job-expriences', [ProfileSettingController::class, 'storeJobExpriences']);
        Route::post('update-notifications', [ProfileSettingController::class, 'updateNotificationSettings']);

        Route::get('request-data/{type}', [FetchServiceController::class, 'requestData']);

        Route::get('get-employers', [ReportController::class, 'getEmployers']);
        Route::get('/generate-single-report', [ReportController::class, 'generateEmpSingleReports']);
        Route::get('/generate-job-hired', [ReportController::class, 'generateJobHired']);
        Route::get('/employer-inprogress', [ReportController::class, 'checkEmployerInprogress']);

        Route::post('/check-employer-report', [ReportController::class, 'checkEmployerReport']);
        Route::post('/generate-employer-report', [ReportController::class, 'generateEmployerReport']);
    });

    // Public Reqeusts ======================================================
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::apiResource('get-in-touch', ContactController::class);

    Route::get('fetch-attributes', [FetchServiceController::class, 'fetchAttributes']);
    Route::get('fetch-categories', [FetchServiceController::class, 'fetchCategories']);

    Route::get('view-jobs', [FetchServiceController::class, 'viewJobs']);
    Route::get('view-job-details/{code}', [FetchServiceController::class, 'viewJobDetails']);
    Route::get('view-categories', [FetchServiceController::class, 'viewCategories']);
});


// Email Verification ===================================
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

// Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
//     ->name('verification.resend');


// Route::get('/update-employer', [EmailVerificationController::class, 'updateEmployer']);
