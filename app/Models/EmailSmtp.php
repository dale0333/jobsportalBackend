<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailSmtp extends Model
{
    use SoftDeletes;
    protected $table = 'email_smtps';

    protected $fillable = [
        'host',
        'port',
        'email',
        'password',
        'encryption',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
