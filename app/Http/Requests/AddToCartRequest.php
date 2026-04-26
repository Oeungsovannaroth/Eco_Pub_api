<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_item_id' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}