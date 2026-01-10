<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'timetable_id',
        'school_id',
        'school_class_id',
        'day',
        'period_index',
        'subject_id',
        'teacher_id',
        'teacher_initials',
    ];
}
