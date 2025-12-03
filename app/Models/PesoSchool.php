<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesoSchool extends Model
{
    use HasFactory;

    protected $table = 'peso_schools';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'school_type',
        'accreditation_status',
        'total_students',
        'courses_offered',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'courses_offered' => 'array', // JSON column
        'total_students' => 'integer',
    ];

    /**
     * Relationship to the User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
