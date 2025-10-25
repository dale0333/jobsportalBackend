<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users table (must come first for foreign keys)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('user_type', ['job_seeker', 'employer', 'peso_school', 'manpower_agency', 'secretariat', 'admin']);
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->text('bio')->nullable();
            $table->string('address')->nullable();
            $table->string('telephone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cover_photo')->nullable();

            $table->boolean('is_web')->default(true);
            $table->boolean('is_email')->default(true);
            $table->boolean('is_sms')->default(false);
            $table->boolean('is_online')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(true);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('job_seekers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('education_level')->nullable();
            $table->string('field_of_study')->nullable();
            $table->json('skills')->nullable();
            $table->json('services')->nullable();
            $table->integer('years_of_experience')->default(0);
            $table->string('preferred_location')->nullable();
            $table->string('expected_salary')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('company_size')->nullable();
            $table->string('industry')->nullable();
            $table->string('locator_number')->nullable();
            $table->timestamps();
        });

        Schema::create('peso_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('school_type')->nullable();
            $table->string('accreditation_status')->nullable();
            $table->integer('total_students')->nullable();
            $table->json('courses_offered')->nullable();
            $table->timestamps();
        });

        Schema::create('manpower_agencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('license_number')->nullable();
            $table->json('services_offered')->nullable();
            $table->integer('years_in_operation')->nullable();
            $table->timestamps();
        });

        Schema::create('social_medias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_seeker_id')->constrained('job_seekers')->cascadeOnDelete();
            $table->string('job_title')->nullable();
            $table->string('company')->nullable();
            $table->string('start_year')->nullable();
            $table->string('end_year')->nullable();
            $table->text('job_description')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->timestamps();
        });

        Schema::create('email_smtps', function (Blueprint $table) {
            $table->id();
            $table->string('host');
            $table->string('port');
            $table->string('email');
            $table->string('password');
            $table->string('encryption');
            $table->boolean('is_active')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bulk_uploads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // File information
            $table->string('original_filename');
            $table->string('filename');
            $table->string('file_path');
            $table->integer('file_size');
            $table->string('file_type');
            $table->string('extension', 10);

            // Upload metadata
            $table->string('purpose')->nullable();

            // Processing statistics
            $table->integer('total_records')->default(0);
            $table->integer('successful_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->json('processing_errors')->nullable();
            $table->json('processing_results')->nullable();

            // Status tracking
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // Indexes for better performance
            $table->index('status');
            $table->index('user_id');
            $table->index('uploaded_at');
            $table->index('processed_at');
        });

        // Laravel default tables (keep these at the end)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('bulk_uploads');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('email_smtps');
        Schema::dropIfExists('user_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('social_medias');
        Schema::dropIfExists('experiences');
        Schema::dropIfExists('manpower_agencies');
        Schema::dropIfExists('peso_schools');
        Schema::dropIfExists('employers');
        Schema::dropIfExists('job_seekers');
        Schema::dropIfExists('users');
    }
};
