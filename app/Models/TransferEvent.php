<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferEvent extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'event_id',
        'station_id',
        'amount',
        'status',
        'source_created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'source_created_at' => 'datetime',
    ];
}
