<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    use HasFactory;

    protected $table = 'references';

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'position',
        'nationality',
        'gender',
        'domicile',
        'status',
        'tem_res_add',
        'tem_province',
        'tem_mun_brgy',
        'per_res_add',
        'per_province',
        'per_mun_brgy',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
