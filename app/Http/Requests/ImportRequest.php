<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'import_type' => 'required|in:aircraft,seat,user',
            'file' => 'required|mimes:xlsx,csv,xls|max:10240',
        ];
    }
}
