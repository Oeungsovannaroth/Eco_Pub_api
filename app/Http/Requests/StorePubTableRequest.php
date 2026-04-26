<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePubTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_number' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:available,reserved,occupied,maintenance'],
        ];
    }
}