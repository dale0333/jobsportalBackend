<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'rate',
    ];

    public function job()
    {
        return $this->belongsTo(JobVacancy::class, 'job_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
