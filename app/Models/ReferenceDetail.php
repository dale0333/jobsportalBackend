<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReferenceDetail extends Model
{
    use HasFactory;

    protected $table = 'reference_details';

    protected $fillable = [
        'reference_id',
        'company',
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

    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }
}
