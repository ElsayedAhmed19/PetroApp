<?php

namespace App\Services\Transfers;

use App\Dtos\TransferEventDto;
use App\Dtos\TransferFilterDto;
use App\Dtos\StationSummaryDto;
use App\Dtos\StoreBatchResultDto;
use App\Http\Requests\StoreTransferBatchRequest;
use App\Repositories\Transfers\BaseTransferRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TransferService implements TransferServiceInterface
{
    public function __construct(private BaseTransferRepository $transferStoreRepo) {}

    /**
     * @param array $events
     */
    public function store(array $events): StoreBatchResultDto
    {
        $strategy = config('event_transfers.batch_strategy', 'partial');

        $categorized = $this->categorizeEventsAfterValidation($events);
        $validEvents = $categorized['validEvents'];
        $failedItems = $categorized['failedItems'];
        $formRequestErrors = $categorized['formRequestErrors'];

        // If strategy is fail-fast and we have ANY failures, throw exception immediately
        if ($strategy === 'fail-fast' && count($failedItems) > 0) {
            throw ValidationException::withMessages($formRequestErrors);
        }

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
        $formRequestErrors = [];

        $rules = StoreTransferBatchRequest::getItemRules();

        foreach ($events as $index => $eventData) {
            $validator = Validator::make($eventData, $rules);

            if ($validator->fails()) {
                // Collect for Partial Accept response
                $failedItems[] = [
                    'index' => $index,
                    'event_id' => $eventData['event_id'] ?? 'N/A',
                    'errors' => $validator->errors()->all(),
                ];

                // Collect for FormRequest-style Exception
                foreach ($validator->errors()->toArray() as $field => $messages) {
                    $formRequestErrors["events.{$index}.{$field}"] = $messages;
                }

                continue;
            }

            $validEvents->push(TransferEventDto::fromArray($eventData));
        }

        return [
            'validEvents' => $validEvents,
            'failedItems' => $failedItems,
            'formRequestErrors' => $formRequestErrors,
        ];
    }
}
