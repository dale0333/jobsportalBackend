<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobConfigDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_config_id',
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function jobConfig()
    {
        return $this->belongsTo(JobConfig::class);
    }
}
