<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatchInputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pax_dates' => 'nullable|string|max:5000',
            'inf_dates' => 'nullable|string|max:5000',
        ];
    }
}
