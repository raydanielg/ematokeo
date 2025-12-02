<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'academic_year',
        'start_date',
        'end_date',
        'term',
        'notes',
    ];

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'exam_school_class');
    }
}
