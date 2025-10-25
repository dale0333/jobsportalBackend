<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('code');

            $table->string('job_sub_category'); // JSON array of sub-category ids
            $table->integer('job_category');
            $table->integer('job_location');
            $table->integer('job_type');
            $table->integer('job_qualify');
            $table->integer('job_level');

            $table->string('job_experience');
            $table->integer('available');
            $table->string('salary')->nullable();

            $table->integer('views')->default(0);
            $table->decimal('rates', 3, 2)->default(0);

            $table->date('deadline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('job_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_vacancies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('job_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_vacancies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('rate', 3, 2);
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_seeker_id')->constrained('job_seekers')->onDelete('cascade');
            $table->foreignId('job_vacancy_id')->constrained('job_vacancies')->onDelete('cascade');
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['pending', 'withdrawn', 'interview', 'rejected', 'hired'])->default('pending');
            $table->timestamps();
        });

        Schema::create('job_application_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained('job_applications')->onDelete('cascade');
            $table->foreignId('process_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'withdrawn', 'interview', 'rejected', 'hired'])->default('pending');
            $table->timestamps();
        });

        // attached files table can be used for resumes and other documents
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->string('name')->nullable();
            $table->string('file_path');
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
        Schema::dropIfExists('job_views');
        Schema::dropIfExists('job_ratings');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('attachments');
    }
};
