<?php

namespace App\Http\Requests;

use App\Rules\Iso8601;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransferBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Add the authorization logic
        return true;
    }

    public function rules(): array
    {
        $strategy = config('transfers.batch_strategy', 'partial');

        if ($strategy === 'fail-fast') {
            $rules = ['events' => ['required', 'array', 'min:1']];

            foreach (self::getItemRules() as $key => $rule) {
                $rules["events.*.{$key}"] = $rule;
            }
            return $rules;
        }

        return [
            'events' => ['required', 'array', 'min:1'],
        ];
    }

    /**
     * Centralized rules for a single transfer event.
     * Shared between FormRequest (fail-fast) and Service (partial-accept).
     */
    public static function getItemRules(): array
    {
        return [
            'event_id' => ['required', 'uuid'],
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'created_at' => ['required', new Iso8601()],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
