<?php

namespace App\DTO;

use App\DTO\AttendanceUserDTO;

/**
 * @OA\Schema(
 *     schema="AttendanceDTO",
 *     type="object",
 *     title="Attendance DTO",
 *     description="Attendance Data Transfer Object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user", ref="#/components/schemas/AttendanceUserDTO"),
 *     @OA\Property(property="attendance_date", type="string", format="date", example="2025-10-06"),
 *     @OA\Property(property="status", type="string", example="Present"),
 *     @OA\Property(property="total_hours", type="number", format="float", example=8.00),
 *     @OA\Property(property="note", type="string", example="Manual entry by admin"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class AttendanceDTO {
    public $id;
    public $user; // AttendanceUserDTO
    public $attendance_date;
    public $status;
    public $total_hours;
    public $note;
    public $created_at;
    public $updated_at;

    public function __construct($attendance)
    {
        $this->id = $attendance->id;
        $this->user = $attendance->user ? new AttendanceUserDTO(
            $attendance->user->id,
            $attendance->user->name,
            $attendance->user->email
        ) : null;

        $this->attendance_date = optional($attendance->attendance_date)->format('Y-m-d') ?? null;
        $this->status = $attendance->status;
        $this->total_hours = $attendance->total_hours;
        $this->note = $attendance->note;
        $this->created_at = optional($attendance->created_at)->toDateTimeString();
        $this->updated_at = optional($attendance->updated_at)->toDateTimeString();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user ? $this->user->toArray() : null,
            'attendance_date' => $this->attendance_date,
            'status' => $this->status,
            'total_hours' => $this->total_hours,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
