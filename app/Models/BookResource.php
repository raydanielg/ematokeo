<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder',
        'title',
        'description',
        'file_path',
        'uploaded_by',
    ];
}
