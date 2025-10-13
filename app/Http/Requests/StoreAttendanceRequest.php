<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization will be handled by middleware (e.g., permission:attendances.manage)
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:100'],
            'total_hours' => ['nullable', 'numeric', 'between:0,999.99'],
            'note' => ['nullable', 'string'],
            // Unique composite rule: no duplicate user_id + attendance_date
            // We enforce with a custom validation rule using Rule::unique with a where clause
            'unique_check' => [
                function ($attribute, $value, $fail) {
                    $userId = $this->input('user_id');
                    $date = $this->input('attendance_date');

                    if ($userId && $date) {
                        $exists = \App\Models\Attendance::where('user_id', $userId)
                            ->where('attendance_date', $date)
                            ->exists();

                        if ($exists) {
                            $fail('An attendance record for this user on the specified date already exists.');
                        }
                    }
                }
            ]
        ];
    }
}
