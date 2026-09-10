<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn () => new EmployeeResource($this->employee)),
            'shiftment_id' => $this->shiftment_id,
            'shiftment' => $this->whenLoaded('shiftment', fn () => new ShiftmentResource($this->shiftment)),
            'attendance_date' => $this->attendance_date?->format('Y-m-d'),
            'description' => $this->description,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'early_in' => $this->early_in,
            'early_out' => $this->early_out,
            'late_in' => $this->late_in,
            'late_out' => $this->late_out,
            'absent' => $this->absent,
            'reason_id' => $this->reason_id,
            'reason' => $this->whenLoaded('reason', fn () => new ReasonResource($this->reason)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
