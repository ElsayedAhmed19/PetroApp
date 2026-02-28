<?php

use App\Http\Controllers\Api\EventTransferController;
use Illuminate\Support\Facades\Route;

Route::post('/transfers', [EventTransferController::class, 'store']);

Route::get('/stations/{stationId}/summary', [EventTransferController::class, 'summary']);
