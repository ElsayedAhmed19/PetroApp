<?php

namespace App\Dtos;

readonly class StationSummaryDto
{
    public function __construct(
        public int $stationId,
        public float $totalApprovedAmount,
        public int $eventsCount,
    ) {}

    /**
     * Create a DTO from an array or provide defaults.
     */
    public static function fromArray(int $stationId, ?array $data): self
    {
        return new self(
            stationId: (int) ($data['station_id'] ?? $stationId),
            totalApprovedAmount: (float) ($data['total_approved_amount'] ?? 0.0),
            eventsCount: (int) ($data['events_count'] ?? 0)
        );
    }
}
