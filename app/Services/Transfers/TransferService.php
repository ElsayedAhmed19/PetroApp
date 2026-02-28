<?php

namespace App\Services\Transfers;

use App\Dtos\TransferEventDto;
use App\Dtos\TransferFilterDto;
use App\Dtos\StationSummaryDto;
use App\Repositories\Transfers\BaseTransferRepository;

class TransferService implements TransferServiceInterface
{
    public function __construct(private BaseTransferRepository $transferStoreRepo) {}

    /**
     * @param array $events
     */
    public function store(array $events): array
    {
        $events = TransferEventDto::collection($events);

        return $this->transferStoreRepo->storeBatch($events);
    }

    public function summary(int $stationId, TransferFilterDto $filters): StationSummaryDto
    {
        return $this->transferStoreRepo->summary($stationId, $filters);
    }
}
