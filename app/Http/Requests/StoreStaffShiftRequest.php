<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'user_id' => 'required|string',

            'name' => 'required|string|max:255',

            'shift_date' => 'required|date',

            'start_time' => 'required',

            'end_time' => 'required',

            'shift_role' => 'required|string|max:100',

            'status' => 'required|in:assigned,completed,absent',
        ];
    }
}
