<?php

namespace App\Services\Transfers;

use App\Dtos\TransferEventDto;
use App\Dtos\TransferFilterDto;
use App\Dtos\StationSummaryDto;

interface TransferServiceInterface
{
    /**
     * @param TransferEventDto[] $events
     */
    public function store(array $events): array;

    public function summary(int $stationId, TransferFilterDto $filters): StationSummaryDto;
}
