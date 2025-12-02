<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\School;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'subject_code',
        'name',
        'class_levels',
        'description',
    ];

    public function teachers()
    {
        return $this->belongsToMany(User::class);
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
