<?php

namespace App\Repositories\Transfers;

use App\Dtos\TransferEventDto;
use App\Models\TransferEvent;
use App\Exceptions\DuplicateEventException;
use Illuminate\Support\Facades\DB;
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
    public function summary(int $stationId): array
    {
        $summary = TransferEvent::where('station_id', $stationId)
            ->select(
                DB::raw('station_id'),
                DB::raw('CAST(SUM(CASE WHEN status = "approved" THEN amount ELSE 0 END) AS DECIMAL(10,3)) as total_approved_amount'),
                DB::raw('COUNT(*) as events_count')
            )
            ->groupBy('station_id')
            ->first();

        return $summary ? $summary->toArray() : [
            'station_id' => $stationId,
            'total_approved_amount' => 0.000,
            'events_count' => 0,
        ];
    }
}
