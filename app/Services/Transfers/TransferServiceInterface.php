<?php

namespace App\Services\Transfers;

use App\Dtos\TransferEventDto;

interface TransferServiceInterface
{
    /**
     * @param TransferEventDto[] $events
     */
    public function store(array $events): array;

    public function summary(int $stationId): array;
}
