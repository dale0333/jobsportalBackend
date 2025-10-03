<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'user_type',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'company_name',
        'contact_person',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Check user type methods
    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    public function isJobSeeker()
    {
        return $this->user_type === 'job_seeker';
    }

    public function isEmployer()
    {
        return $this->user_type === 'employer';
    }

    public function isPesoSchool()
    {
        return $this->user_type === 'peso_school';
    }

    public function isManpowerAgency()
    {
        return $this->user_type === 'manpower_agency';
    }

    // Create token with user type abilities
    public function createTokenWithAbilities($name = 'auth-token', $abilities = null)
    {
        if (!$abilities) {
            $abilities = $this->getDefaultAbilities();
        }

        return $this->createToken($name, $abilities);
    }

    protected function getDefaultAbilities()
    {
        return match ($this->user_type) {
            'admin' => ['admin:*', 'users:*', 'reports:*'],
            'employer' => ['employer:*', 'jobs:manage', 'applications:manage'],
            'job_seeker' => ['jobseeker:*', 'jobs:view', 'applications:create'],
            'peso_school' => ['peso:*', 'bulk-upload:*', 'reports:view'],
            'manpower_agency' => ['manpower:*', 'reports:create'],
            default => ['user:basic'],
        };
    }

    // Relationships
    public function jobSeeker()
    {
        return $this->hasOne(JobSeeker::class);
    }

    public function employer()
    {
        return $this->hasOne(Employer::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('user_type', 'admin');
    }

    public function scopeJobSeekers($query)
    {
        return $query->where('user_type', 'job_seeker');
    }

    public function scopeEmployers($query)
    {
        return $query->where('user_type', 'employer');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
