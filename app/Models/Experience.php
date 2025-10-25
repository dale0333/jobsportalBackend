<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_seeker_id',
        'job_title',
        'company',
        'start_year',
        'end_year',
        'job_description'
    ];
}
