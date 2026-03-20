<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PesoStudent extends Model
{
    use HasFactory;

    protected $table = 'peso_students';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'gender',
        'contact',
        'education_level',
        'field_of_study',
        'skills',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'type');
    }

    public function jobs()
    {
        return $this->hasMany(JobVacancy::class, 'job_category', 'type');
    }
}
