<?php

namespace App\Http\Requests;

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
        return [
            'events' => ['required', 'array', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
