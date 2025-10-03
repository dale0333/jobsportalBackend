<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobConfig extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'icon',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function jobConfigDetil()
    {
        return $this->hasOne(JobConfigDetail::class);
    }

    public function jobConfigDetails()
    {
        return $this->hasMany(JobConfigDetail::class);
    }
}
