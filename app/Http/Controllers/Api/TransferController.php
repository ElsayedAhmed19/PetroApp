<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransferBatchRequest;
use Illuminate\Http\JsonResponse;

class TransferController extends Controller
{
    public function __construct() {}

    /**
     * POST api/transfers
     */
    public function store(StoreTransferBatchRequest $request): JsonResponse
    {
        return response()->json($request->validated(), 201);
    }
}
