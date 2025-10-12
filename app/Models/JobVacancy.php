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
        'content',
        'code',
        'job_category',
        'job_sub_category',
        'job_location',
        'job_type',
        'job_qualify',
        'job_level',
        'job_experience',
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
        return $this->belongsTo(JobConfigDetail::class, $column);
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

    // ===================================================
    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function applicants()
    {
        return $this->belongsToMany(JobSeeker::class, 'job_applications')
            ->withTimestamps()
            ->withPivot('status', 'cover_letter');
    }




    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('application_deadline', '<', now());
    }

    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeWithSkills($query, array $skills)
    {
        return $query->where(function ($q) use ($skills) {
            foreach ($skills as $skill) {
                $q->orWhereJsonContains('required_skills', $skill);
            }
        });
    }
}
