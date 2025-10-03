<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageStatus extends Model
{
    use HasFactory;
    protected $table = 'message_statuses';
    protected $fillable = [
        'message_id',
        'is_sent',
        'is_delivered',
        'is_seen'
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
