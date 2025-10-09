<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = [
        'name',
        'file_path',
        'type',
    ];

    // Polymorphic relation
    public function attachable()
    {
        return $this->morphTo();
    }

    // Accessor to get full URL
    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }
}
