<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'audience',
        'channel',
        'exam_id',
        'class_level',
        'message',
        'status',
        'recipient_scope',
        'created_by',
    ];

    protected $casts = [
        'recipient_scope' => 'array',
    ];
}
