<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'registration_number',
        'school_code',
        'level',
        'ownership',
        'phone',
        'email',
        'address',
        'district',
        'region',
        'head_teacher_name',
        'head_teacher_phone',
        'logo_path',
    ];
}
