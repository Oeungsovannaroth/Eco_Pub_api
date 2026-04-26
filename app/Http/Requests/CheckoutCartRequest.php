<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'table_id' => ['required', 'string'], // user enters T04, T05
        'reservation_id' => ['nullable', 'string'],
        'order_type' => ['required', 'in:dine_in,takeaway,reservation'],
        'note' => ['nullable', 'string'],
    ];
}
}