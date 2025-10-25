<?php
// app/Models/BulkUpload.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_filename',
        'filename',
        'file_path',
        'file_size',
        'file_type',
        'extension',
        'purpose',
        'status',
        'uploaded_at',
        'processed_at',
        'processing_results',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
        'processed_at' => 'datetime',
        'processing_results' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
