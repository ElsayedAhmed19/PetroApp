<?php

namespace App\Http\Controllers\Api;

use App\Dtos\TransferFilterDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransferBatchRequest;
use App\Http\Requests\TransferSummaryRequest;
use App\Http\Resources\StoreBatchResource;
use App\Http\Resources\TransferSummaryResource;
use App\Services\Transfers\TransferServiceInterface;
use Illuminate\Http\JsonResponse;

class EventTransferController extends Controller
{
    public function __construct(private TransferServiceInterface $transferService) {}

    /**
     * POST api/transfers
     */
    public function store(StoreTransferBatchRequest $request): JsonResponse
    {
        $result = $this->transferService->store($request->validated()['events']);

        return $this->postJsonSuccessResponse(new StoreBatchResource($result));
    }

    /**
     * GET api/stations/{stationId}/summary
     */
    public function summary(TransferSummaryRequest $request): JsonResponse
    {
        $filters = TransferFilterDto::fromArray($request->validated());

        $summary = $this->transferService->summary($request->stationId, $filters);

        return $this->getJsonSuccessResponse(new TransferSummaryResource($summary));
    }
}
