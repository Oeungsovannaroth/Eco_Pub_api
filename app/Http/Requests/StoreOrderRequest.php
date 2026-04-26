<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string'],
            'table_id' => ['required', 'string'],
            'reservation_id' => ['nullable', 'string'],
            'order_type' => ['required', 'in:dine_in,takeaway,reservation'],
            'order_status' => ['required', 'in:pending,preparing,served,completed,cancelled'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string'],
        ];
    }
}