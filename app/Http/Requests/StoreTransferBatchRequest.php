<?php

namespace App\Http\Requests;

use App\Enums\TransferStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTransferBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Add the authorization logic
        return true;
    }

    public function rules(): array
    {
        return [
            'events' => ['required', 'array', 'min:1'],
            'events.*.event_id' => ['required', 'uuid'],
            'events.*.station_id' => ['required', 'integer', 'exists:stations,id'],
            'events.*.amount' => ['required', 'numeric', 'gt:0'],
            'events.*.status' => ['required', 'string', new Enum(TransferStatus::class)],
            'events.*.created_at' => ['required', 'date_format:Y-m-d\TH:i:s\Z'],
        ];
    }

    public function messages(): array
    {
        return [
            'events.*.created_at.date_format' => 'The created_at field must be in ISO8601 format (e.g., 2026-02-19T10:00:00Z).',
            'events.*.amount.gt' => 'The amount must be greater than zero.',
            'events.*.station_id.exists' => 'The selected station_id is invalid.',
            'events.*.status' => 'The status must be a valid transfer status (' . implode(', ', array_column(TransferStatus::cases(), 'value')) . ').',
        ];
    }
}
