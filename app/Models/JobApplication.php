<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_seeker_id',
        'job_vacancy_id',
        'cover_letter',
        'status',
        'admin_notes',
        'applied_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    // Relationships
    public function jobSeeker()
    {
        return $this->belongsTo(JobSeeker::class);
    }

    public function jobVacancy()
    {
        return $this->belongsTo(JobVacancy::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeShortlisted($query)
    {
        return $query->where('status', 'shortlisted');
    }

    public function scopeForInterview($query)
    {
        return $query->where('status', 'interview');
    }

    public function scopeHired($query)
    {
        return $query->where('status', 'hired');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Helper methods
    public function markAsShortlisted()
    {
        $this->update(['status' => 'shortlisted']);
    }

    public function markAsRejected()
    {
        $this->update(['status' => 'rejected']);
    }

    public function markAsHired()
    {
        $this->update(['status' => 'hired']);
    }
}
