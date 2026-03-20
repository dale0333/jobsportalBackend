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
        // reviewing = 0
        // Interview = 1
        // HIRED = 2
        // Not Qualified =3
        // For Further Evaluation = 4
        // Notified but Non Appearance =5

        // public function getStatusLabelAttribute()
        // {
        //     return match ($this->status) {
        //         0 => 'Reviewing',
        //         1 => 'Interview',
        //         2 => 'Hired',
        //         3 => 'Not Qualified',
        //         4 => 'For Evaluation',
        //         5 => 'No Show',
        //         default => 'Unknown'
        //     };
        // }

        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('employers')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('code');

            $table->string('job_sub_category');
            $table->integer('job_category');
            $table->integer('job_location');
            $table->integer('job_type');
            $table->integer('job_qualify');
            $table->integer('job_level');
            $table->integer('job_experience');

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

            $table->foreignId('job_seeker_id')
                ->constrained('job_seekers')
                ->onDelete('cascade');

            $table->foreignId('job_vacancy_id')
                ->constrained('job_vacancies')
                ->onDelete('cascade');

            // applied / matched / invited
            $table->string('type')->nullable();

            $table->text('cover_letter')->nullable();

            // current status
            $table->tinyInteger('status')->default(0);

            // invitation accepted?
            $table->tinyInteger('is_accepted')->default(0);

            $table->date('date_status')->nullable();

            $table->timestamps();
        });

        Schema::create('job_application_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_application_id')
                ->constrained('job_applications')
                ->onDelete('cascade');

            $table->foreignId('process_by')
                ->constrained('users')
                ->onDelete('cascade');

            $table->tinyInteger('status')->nullable();

            $table->text('notes')->nullable();

            $table->date('finalized_date')->nullable();

            $table->timestamps();
        });

        Schema::create('job_favorites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_id')
                ->constrained('job_vacancies')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->boolean('is_favorite')->default(true);

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['job_id', 'user_id']);
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
        Schema::dropIfExists('job_favorites');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('attachments');
    }
};
