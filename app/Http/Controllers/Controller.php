<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

abstract class Controller
{
    public function postJsonSuccessResponse($data = []): JsonResponse
    {
        return response()->json($data, Response::HTTP_CREATED);
    }

    public function getJsonSuccessResponse($data = []): JsonResponse
    {
        return response()->json($data, Response::HTTP_OK);
    }
}
