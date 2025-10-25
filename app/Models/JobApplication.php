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
    ];

    // Polymorphic relationship for attachments
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function jobApplicationTransactions()
    {
        return $this->hasMany(JobApplicationTransaction::class);
    }

    public function jobSeeker()
    {
        return $this->belongsTo(JobSeeker::class);
    }

    public function jobVacancy()
    {
        return $this->belongsTo(JobVacancy::class);
    }
}
