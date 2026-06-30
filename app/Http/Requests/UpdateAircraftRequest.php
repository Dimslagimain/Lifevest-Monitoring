<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAircraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'airline_id' => 'required|exists:airlines,id',
            'type' => 'required',
            'status' => 'required|in:active,prolong',
            'pn_adult' => 'nullable|string|max:50',
            'pn_crew' => 'nullable|string|max:50',
            'pn_infant' => 'nullable|string|max:50',
        ];
    }
}
