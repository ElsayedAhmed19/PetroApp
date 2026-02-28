<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Add the authorization logic
        return true;
    }

    public function rules(): array
    {
        return [
            'stationId' => ['required', 'integer', 'exists:stations,id'],
            'status' => ['nullable', 'string', 'in:approved'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'stationId' => $this->route('stationId'),
        ]);
    }

    public function messages(): array
    {
        return [];
    }
}
