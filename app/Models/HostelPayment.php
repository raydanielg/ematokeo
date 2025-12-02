<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostelPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'allocation_id',
        'amount',
        'paid_on',
        'method',
        'reference',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'paid_on' => 'date',
    ];

    public function allocation()
    {
        return $this->belongsTo(HostelAllocation::class, 'allocation_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
