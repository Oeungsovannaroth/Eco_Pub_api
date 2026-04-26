<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'table_id' => ['required', 'string'],
            'reservation_date' => ['required', 'date'],
            'reservation_time' => ['required', 'date_format:H:i'],
            'guest_count' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
            'special_request' => ['nullable', 'string'],
        ];
    }
}