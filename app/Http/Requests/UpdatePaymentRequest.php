<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['sometimes', 'required', 'string'],
            'payment_method' => ['sometimes', 'required', 'in:cash,card,qr'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'payment_status' => ['sometimes', 'required', 'in:unpaid,paid,refunded'],
            'transaction_code' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}