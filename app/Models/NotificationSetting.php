<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_from_name',
        'email_from_address',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'sms_provider_name',
        'sms_api_url',
        'sms_sender_id',
        'sms_api_key',
        'sms_callback_url',
    ];
}
