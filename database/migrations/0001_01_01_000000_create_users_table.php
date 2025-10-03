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
            $table->enum('user_type', ['job_seeker', 'employer', 'peso_school', 'manpower_agency', 'admin']);
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        // Job seekers table
        Schema::create('job_seekers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('education_level')->nullable();
            $table->string('field_of_study')->nullable();
            $table->json('skills')->nullable();
            $table->integer('years_of_experience')->default(0);
            $table->string('current_location')->nullable();
            $table->string('preferred_location')->nullable();
            $table->decimal('expected_salary', 10, 2)->nullable();
            $table->string('resume_file_path')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // Employers table
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_size')->nullable();
            $table->string('industry')->nullable();
            $table->string('company_address')->nullable();
            $table->string('website')->nullable();
            $table->text('company_description')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // PESO Schools table
        Schema::create('peso_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('school_type')->nullable();
            $table->string('accreditation_status')->nullable();
            $table->integer('total_students')->nullable();
            $table->json('courses_offered')->nullable();
            $table->string('school_address')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // Manpower Agencies table
        Schema::create('manpower_agencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('license_number')->nullable();
            $table->json('services_offered')->nullable();
            $table->integer('years_in_operation')->nullable();
            $table->string('agency_address')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // Job vacancies table -
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('code');

            $table->integer('job_service');
            $table->integer('job_location');
            $table->integer('job_type');
            $table->integer('job_qualify');
            $table->integer('job_level');

            $table->string('job_experince');
            $table->string('salary')->nullable();

            $table->date('deadline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Job applications table
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_seeker_id')->constrained('job_seekers')->onDelete('cascade');
            $table->foreignId('job_vacancy_id')->constrained('job_vacancies')->onDelete('cascade');
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['pending', 'shortlisted', 'interview', 'rejected', 'hired'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->unique(['job_seeker_id', 'job_vacancy_id']);
        });

        // Trainings table (for PESO Schools)
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peso_school_id')->constrained('peso_schools')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('category');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location');
            $table->integer('max_participants')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Training applications table
        Schema::create('training_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_seeker_id')->constrained('job_seekers')->onDelete('cascade');
            $table->foreignId('training_id')->constrained('trainings')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->unique(['job_seeker_id', 'training_id']);
        });

        // Overseas jobs table (for Manpower Agencies)
        Schema::create('overseas_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manpower_agency_id')->constrained('manpower_agencies')->onDelete('cascade');
            $table->string('job_title');
            $table->text('job_description');
            $table->string('country');
            $table->string('city');
            $table->string('industry');
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('contract_period')->nullable();
            $table->json('requirements')->nullable();
            $table->json('benefits')->nullable();
            $table->date('application_deadline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Overseas job applications table
        Schema::create('overseas_job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_seeker_id')->constrained('job_seekers')->onDelete('cascade');
            $table->foreignId('overseas_job_id')->constrained('overseas_jobs')->onDelete('cascade');
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['pending', 'shortlisted', 'processing', 'rejected', 'approved'])->default('pending');
            $table->text('agency_notes')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->unique(['job_seeker_id', 'overseas_job_id']);
        });

        // Reports table
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('report_type', ['manpower', 'employment', 'attrition', 'monthly_manpower', 'training', 'overseas']);
            $table->string('period');
            $table->json('report_data');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        // Notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
        });

        // Bulk uploads table (for PESO/School)
        Schema::create('bulk_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('filename');
            $table->string('file_path');
            $table->integer('total_records');
            $table->integer('successful_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->json('processing_errors')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });

        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
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
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('overseas_job_applications');
        Schema::dropIfExists('overseas_jobs');
        Schema::dropIfExists('training_applications');
        Schema::dropIfExists('trainings');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_vacancies');
        Schema::dropIfExists('manpower_agencies');
        Schema::dropIfExists('peso_schools');
        Schema::dropIfExists('employers');
        Schema::dropIfExists('job_seekers');
        Schema::dropIfExists('email_smtps');
        Schema::dropIfExists('user_logs');
        Schema::dropIfExists('users');
    }
};
