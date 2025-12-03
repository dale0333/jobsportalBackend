<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManpowerAgency extends Model
{
    use HasFactory;

    protected $table = 'manpower_agencies';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'license_number',
        'services_offered',
        'years_in_operation',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'services_offered' => 'array', // JSON column
        'years_in_operation' => 'integer',
    ];

    /**
     * Relationship to the User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
