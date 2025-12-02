<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostelRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostel_id',
        'name',
        'capacity',
        'occupied',
        'available',
        'notes',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }
}
