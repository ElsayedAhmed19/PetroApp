<?php

namespace App\Services\Transfers;

use App\Dtos\TransferEventDto;
use App\Repositories\Transfers\TransferStoreRepoInterface;

class TransferService implements TransferServiceInterface
{
    public function __construct(private TransferStoreRepoInterface $transferStoreRepo) {}

    /**
     * @param array $events
     */
    public function store(array $events): array
    {
        $events = TransferEventDto::collection($events);

        return $this->transferStoreRepo->storeBatch($events);
    }
}
