<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostelAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'hostel_id',
        'hostel_room_id',
        'academic_year',
        'fee_amount',
        'status',
        'guardian_name',
        'guardian_phone',
        'guardian_relationship',
        'notes',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function room()
    {
        return $this->belongsTo(HostelRoom::class, 'hostel_room_id');
    }

    public function payments()
    {
        return $this->hasMany(HostelPayment::class, 'allocation_id');
    }
}
