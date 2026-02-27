<?php

namespace App\Repositories\Transfers;

use App\Dtos\TransferEventDto;
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
}
