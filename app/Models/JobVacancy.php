<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasFactory;
    protected $table = 'job_vacancies';

    protected $fillable = [
        'employer_id',
        'title',
        'description',
        'qualifications',
        'code',
        'job_category',
        'job_sub_category',
        'job_location',
        'job_type',
        'job_qualify',
        'job_level',
        'job_experience',
        'available',
        'salary',
        'views',
        'rates',
        'deadline',
        'is_active',
    ];

    protected $casts = [
        'job_sub_category' => 'array',
        'deadline' => 'date',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'job_category');
    }

    // attributes
    public function jobDetails($column)
    {
        return $this->belongsTo(SubAttribute::class, $column);
    }

    public function jobLocation()
    {
        return $this->jobDetails('job_location');
    }

    public function jobType()
    {
        return $this->jobDetails('job_type');
    }

    public function jobQualify()
    {
        return $this->jobDetails('job_qualify');
    }

    public function jobLevel()
    {
        return $this->jobDetails('job_level');
    }

    public function jobExperience()
    {
        return $this->jobDetails('job_experience');
    }


    // views and ratings
    public function views()
    {
        return $this->hasMany(JobView::class, 'job_id');
    }

    public function ratings()
    {
        return $this->hasMany(JobRating::class, 'job_id');
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rate') ?? 0;
    }

    // Relationships
    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class, 'job_vacancy_id');
    }

    public function pesoStudents()
    {
        return $this->hasMany(PesoStudent::class, 'type', 'job_category');
    }

    public function applicants()
    {
        return $this->belongsToMany(JobSeeker::class, 'job_applications')
            ->withTimestamps()
            ->withPivot('status', 'cover_letter');
    }
}
