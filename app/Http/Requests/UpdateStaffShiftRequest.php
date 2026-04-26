<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'    => 'sometimes|string',
            'shift_date' => 'sometimes|date',
            'start_time' => 'sometimes',
            'end_time'   => 'sometimes|after:start_time',
            'shift_role' => 'sometimes|string',
            'status'     => 'sometimes|in:assigned,completed,absent'
        ];
    }
}
