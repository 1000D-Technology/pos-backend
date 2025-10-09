<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $attendanceId = $this->route('attendance') ?? $this->route('id');

        return [
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'attendance_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'string', 'max:100'],
            'total_hours' => ['nullable', 'numeric', 'between:0,999.99'],
            'note' => ['nullable', 'string'],
            // Custom uniqueness check that excludes the current record
            'unique_check' => [
                function ($attribute, $value, $fail) use ($attendanceId) {
                    $userId = $this->input('user_id') ?? null;
                    $date = $this->input('attendance_date') ?? null;

                    // If either user_id or date not present, use the existing attendance data
                    if (!$userId || !$date) {
                        $attendance = \App\Models\Attendance::find($attendanceId);
                        if ($attendance) {
                            $userId = $userId ?? $attendance->user_id;
                            // attendance_date may be a Carbon instance or a string depending on casts; normalize to Y-m-d
                            if (! $date) {
                                $attDate = $attendance->attendance_date;
                                if (is_object($attDate) && method_exists($attDate, 'format')) {
                                    $date = $attDate->format('Y-m-d');
                                } else {
                                    $date = (string) $attDate;
                                }
                            }
                        }
                    }

                    if ($userId && $date) {
                        $exists = \App\Models\Attendance::where('user_id', $userId)
                            ->where('attendance_date', $date)
                            ->where('id', '<>', $attendanceId)
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
