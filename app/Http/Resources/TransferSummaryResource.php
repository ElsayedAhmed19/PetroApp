<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'station_id' => $this->stationId,
            'total_approved_amount' => $this->totalApprovedAmount,
            'events_count' => $this->eventsCount,
        ];
    }
}
