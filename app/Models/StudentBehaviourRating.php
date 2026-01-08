<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBehaviourRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'exam_id',
        'school_id',
        'code',
        'label',
        'grade',
        'rated_date',
        'comment',
        'created_by',
    ];

    protected $casts = [
        'rated_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
