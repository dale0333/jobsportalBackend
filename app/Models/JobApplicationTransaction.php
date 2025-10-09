<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplicationTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_application_id',
        'process_by',
        'notes',
        'status',
    ];

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'process_by');
    }
}
