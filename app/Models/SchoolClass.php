<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\School;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'parent_class_id',
        'name',
        'level',
        'stream',
        'description',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
