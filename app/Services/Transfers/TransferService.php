<?php

namespace App\Services\Transfers;

use App\Dtos\TransferEventDto;
use App\Dtos\TransferFilterDto;
use App\Dtos\StationSummaryDto;
use App\Dtos\StoreBatchResultDto;
use App\Repositories\Transfers\BaseTransferRepository;
use App\Rules\Iso8601;
use Illuminate\Support\Facades\Validator;

class TransferService implements TransferServiceInterface
{
    public function __construct(private BaseTransferRepository $transferStoreRepo) {}

    /**
     * @param array $events
     */
    public function store(array $events): StoreBatchResultDto
    {
        [
            'validEvents' => $validEvents,
            'failedItems' => $failedItems
        ] = $this->categorizeEventsAfterValidation($events);

        $result = $this->transferStoreRepo->storeBatch($validEvents);

        return new StoreBatchResultDto(
            inserted: $result->inserted,
            duplicates: $result->duplicates,
            validation_failed_items: $failedItems
        );
    }

    public function summary(int $stationId, TransferFilterDto $filters): StationSummaryDto
    {
        return $this->transferStoreRepo->summary($stationId, $filters);
    }

    private function categorizeEventsAfterValidation(array $events): array
    {
        $validEvents = collect();
        $failedItems = [];

        $rules = [
            'event_id' => ['required', 'uuid'],
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'created_at' => ['required', new Iso8601()],
        ];

        foreach ($events as $index => $eventData) {
            $validator = Validator::make($eventData, $rules);

            if ($validator->fails()) {
                $failedItems[] = [
                    'order_at_file' => $index,
                    'event_id' => $eventData['event_id'] ?? 'N/A',
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            $validEvents->push(TransferEventDto::fromArray($eventData));
        }

        return [
            'validEvents' => $validEvents,
            'failedItems' => $failedItems,
        ];
    }
}
