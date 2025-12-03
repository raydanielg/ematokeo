<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_number',
        'full_name',
        'class_level',
        'stream',
        'gender',
        'date_of_birth',
        'school_id',
        'academic_year',
        'division',
    ];

    public function results()
    {
        return $this->hasMany(\App\Models\ExamResult::class);
    }
}
