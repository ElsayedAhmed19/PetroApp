<?php

namespace App\Repositories\Transfers;

use App\Dtos\TransferEventDto;
use App\Models\TransferEvent;
use App\Exceptions\DuplicateEventException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseTransferRepository
{
    /**
     * @param TransferEventDto[] $events
     * @return array{inserted: int, duplicates: int}
     */
    public function storeBatch(Collection $events): array
    {
        $insertedCount = 0;
        $duplicateCount = 0;

        foreach ($events as $eventDto) {
            try {
                // Idempotency check
                if ($this->exists($eventDto->eventId)) {
                    Log::error("Duplicate event {$eventDto->eventId} detected at db level");

                    $duplicateCount++;
                    continue;
                }

                // Persistence logic
                $this->save($eventDto);
                $insertedCount++;
            } catch (DuplicateEventException $e) {
                // Throwing a duplicate exception via DB unique constraint
                Log::error("Duplicate event {$eventDto->eventId} detected at concurrent execution");

                $duplicateCount++;
            } catch (Throwable $e) {
                // TODO: Logger can be updated later using strategy pattern for more flexibility
                Log::error("Critical failure during transfer ingestion {$eventDto->eventId}: " . $e->getMessage());
                throw $e;
            }
        }

        return [
            'inserted' => $insertedCount,
            'duplicates' => $duplicateCount,
        ];
    }

    /**
     * Check if the event already exists in storage.
     */
    abstract public function exists(string $eventId): bool;

    /**
     * Persist the event to storage.
     * @throws DuplicateEventException
     */
    abstract public function save(TransferEventDto $eventDto): TransferEvent;

    /**
     * Get the summary of transfers for a station.
     * @return array{station_id: int, total_approved_amount: float, events_count: int}
     */
    abstract public function summary(int $stationId): array;
}
