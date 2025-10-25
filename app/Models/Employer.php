<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_size',
        'industry',
        'locator_number'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }













    public function jobVacancies()
    {
        return $this->hasMany(JobVacancy::class);
    }

    public function jobApplications()
    {
        return $this->hasManyThrough(JobApplication::class, JobVacancy::class);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }
}
