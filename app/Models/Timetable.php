<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'title',
        'table_no',
        'name',
        'class_name',
        'academic_year',
        'term',
        'file_path',
        'schedule_json',
        'is_published',
    ];

    protected $casts = [
        'schedule_json' => 'array',
        'is_published' => 'boolean',
    ];
}
