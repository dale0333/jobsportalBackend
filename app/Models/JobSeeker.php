<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSeeker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'gender',
        'education_level',
        'field_of_study',
        'skills',
        'years_of_experience',
        'current_location',
        'preferred_location',
        'expected_salary',
        'resume_file_path',
        'bio',
        'is_available',
    ];

    protected $casts = [
        'skills' => 'array',
        'date_of_birth' => 'date',
        'expected_salary' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function appliedJobs()
    {
        return $this->belongsToMany(JobVacancy::class, 'job_applications')
            ->withTimestamps()
            ->withPivot('status', 'cover_letter');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeWithSkills($query, array $skills)
    {
        return $query->where(function ($q) use ($skills) {
            foreach ($skills as $skill) {
                $q->orWhereJsonContains('skills', $skill);
            }
        });
    }

    public function scopeInLocation($query, $location)
    {
        return $query->where('preferred_location', 'like', "%{$location}%")
            ->orWhere('current_location', 'like', "%{$location}%");
    }
}
