<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamStudentResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'exam_id',
        'student_id',
        'academic_year',
        'total',
        'average',
        'grade',
        'division',
        'points',
    ];
}
