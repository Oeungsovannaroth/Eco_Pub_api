<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['sometimes', 'required', 'string', 'max:255'],
            'table_id' => ['sometimes', 'required', 'string'],
            'reservation_date' => ['sometimes', 'required', 'date'],
            'reservation_time' => ['sometimes', 'required', 'date_format:H:i'],
            'guest_count' => ['sometimes', 'required', 'integer', 'min:1'],
            'status' => ['sometimes', 'required', 'in:pending,confirmed,cancelled,completed'],
            'special_request' => ['nullable', 'string'],
        ];
    }
}