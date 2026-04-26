<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'string'],
            'payment_method' => ['required', 'in:cash,card,qr'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:unpaid,paid,refunded'],
            'transaction_code' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}