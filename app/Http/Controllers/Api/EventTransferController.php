<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransferBatchRequest;
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

        return response()->json($result, 201);
    }
}
