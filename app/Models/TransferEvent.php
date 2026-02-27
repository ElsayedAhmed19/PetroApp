<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferEvent extends Model
{
    use HasFactory;

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

    /**
     * Get the station that owns the transfer.
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
