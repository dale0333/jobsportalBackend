<?php
// app/Models/Report.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'report_type',
        'period',
        'report_data',
        'notes',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'report_data' => 'array',
        'submitted_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeManpower($query)
    {
        return $query->where('report_type', 'manpower');
    }

    public function scopeEmployment($query)
    {
        return $query->where('report_type', 'employment');
    }

    public function scopeAttrition($query)
    {
        return $query->where('report_type', 'attrition');
    }

    public function scopeMonthly($query)
    {
        return $query->where('report_type', 'monthly_manpower');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Helper methods
    public function submit()
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now()
        ]);
    }
}
