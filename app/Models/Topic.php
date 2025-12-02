<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'class_level',
        'title',
        'description',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
