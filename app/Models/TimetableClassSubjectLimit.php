<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;

class TimetableClassSubjectLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_class_id',
        'subject_id',
        'periods_per_week',
        'is_double',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
