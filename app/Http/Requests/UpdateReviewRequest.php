<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'menu_item_id' => 'sometimes|string',
        'rating' => 'sometimes|integer|min:1|max:5',
        'comment' => 'nullable|string'
    ];
}
}
