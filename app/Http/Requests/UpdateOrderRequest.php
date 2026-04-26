<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'required', 'string'],
            'table_id' => ['sometimes', 'required', 'string'],
            'reservation_id' => ['nullable', 'string'],
            'order_type' => ['sometimes', 'required', 'in:dine_in,takeaway,reservation'],
            'order_status' => ['sometimes', 'required', 'in:pending,preparing,served,completed,cancelled'],
            'note' => ['nullable', 'string'],
        ];
    }
}