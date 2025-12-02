<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostelRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'hostel_id',
        'class_level',
        'academic_year',
        'title',
        'items',
        'rules',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }
}
