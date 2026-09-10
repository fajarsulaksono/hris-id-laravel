<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn () => new EmployeeResource($this->employee)),
            'year' => $this->year,
            'month' => $this->month,
            'total_workday' => $this->total_workday,
            'total_in' => $this->total_in,
            'total_loyality' => $this->total_loyality,
            'total_absent' => $this->total_absent,
            'total_overtime' => $this->total_overtime,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
