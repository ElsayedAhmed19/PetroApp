<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Batch Processing Strategy
    |--------------------------------------------------------------------------
    |
    | This option defines how the system handles validation failures in a batch.
    | 'partial'  : Stores valid events and reports failures individually.
    | 'fail-fast': Rejects the entire batch if any event is invalid.
    |
    */
    'batch_strategy' => env('EVENT_TRANSFERS_BATCH_STRATEGY', 'partial'),
];
