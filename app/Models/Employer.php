<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_person',
        'position',
        'company_size',
        'locator_number',
        'industry',
        'sub_industry',
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

    public function references()
    {
        return $this->hasMany(Reference::class, 'user_id', 'user_id');
    }
}
