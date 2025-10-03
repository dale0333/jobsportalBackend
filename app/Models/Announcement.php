<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;
    protected $table = 'announcements';
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
