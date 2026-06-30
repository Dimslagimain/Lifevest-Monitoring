<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAircraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'registration' => 'required|unique:aircraft,registration',
            'airline_id' => 'required|exists:airlines,id',
            'type' => 'required',
            'layout' => 'required',
            'status' => 'required|in:active,prolong',
        ];
    }
}
