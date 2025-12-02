<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SittingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'class_id',
        'title',
        'rooms_count',
        'file_path',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
