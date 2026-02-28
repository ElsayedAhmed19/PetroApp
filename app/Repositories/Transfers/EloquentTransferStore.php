<?php

namespace App\Repositories\Transfers;

use App\Dtos\TransferEventDto;
use App\Dtos\TransferFilterDto;
use App\Dtos\StationSummaryDto;
use App\Models\TransferEvent;
use App\Exceptions\DuplicateEventException;
use Throwable;

class EloquentTransferStore extends BaseTransferRepository
{
    /**
     * @inheritDoc
     * @throws DuplicateEventException
     */
    public function save(TransferEventDto $eventDto): TransferEvent
    {
        try {
            return TransferEvent::create([
                'event_id' => $eventDto->eventId,
                'station_id' => $eventDto->stationId,
                'amount' => $eventDto->amount,
                'status' => $eventDto->status,
                'source_created_at' => $eventDto->createdAt,
            ]);
        } catch (Throwable $e) {
            // Extract database uniqueness exception into domain exception
            if (str_contains($e->getMessage(), 'Duplicate entry') || $e->getCode() == '23000') {
                throw new DuplicateEventException($e->getMessage(), (int) $e->getCode(), $e);
            }

            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function exists(string $eventId): bool
    {
        return TransferEvent::where('event_id', $eventId)->exists();
    }

    /**
     * @inheritDoc
     */
    public function summary(int $stationId, TransferFilterDto $filters): StationSummaryDto
    {
        $approvedStatus = 'approved';
        $statusFilter = $filters->status;

        $summary = TransferEvent::where('station_id', $stationId)
            ->selectRaw('station_id')
            ->selectRaw("SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as total_approved_amount", [$approvedStatus])
            ->when($statusFilter, function ($query) use ($statusFilter) {
                return $query->selectRaw("COUNT(CASE WHEN status = ? THEN 1 END) as events_count", [$statusFilter]);
            }, function ($query) {
                return $query->selectRaw("COUNT(*) as events_count");
            })
            ->groupBy('station_id')
            ->first();

        return StationSummaryDto::fromArray($stationId, $summary?->getAttributes());
    }
}
