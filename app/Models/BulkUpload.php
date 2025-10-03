<?php
// app/Models/BulkUpload.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploaded_by',
        'filename',
        'file_path',
        'total_records',
        'successful_records',
        'failed_records',
        'processing_errors',
        'status',
    ];

    protected $casts = [
        'processing_errors' => 'array',
    ];

    // Relationships
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Helper methods
    public function markAsProcessing()
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsCompleted($successful, $failed, $errors = null)
    {
        $this->update([
            'status' => 'completed',
            'successful_records' => $successful,
            'failed_records' => $failed,
            'processing_errors' => $errors
        ]);
    }

    public function markAsFailed($errors = null)
    {
        $this->update([
            'status' => 'failed',
            'processing_errors' => $errors
        ]);
    }
}
