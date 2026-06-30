<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seat_ids' => 'required|array',
            'seat_ids.*' => 'required|string',
            'expiry_date' => 'required|date',
        ];
    }
}
