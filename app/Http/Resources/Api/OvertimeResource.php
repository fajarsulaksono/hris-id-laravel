<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OvertimeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn () => new EmployeeResource($this->employee)),
            'shiftment_id' => $this->shiftment_id,
            'shiftment' => $this->whenLoaded('shiftment', fn () => new ShiftmentResource($this->shiftment)),
            'overtime_date' => $this->overtime_date?->format('Y-m-d'),
            'start_hour' => $this->start_hour,
            'end_hour' => $this->end_hour,
            'raw_value' => $this->raw_value,
            'calculated_value' => $this->calculated_value,
            'holiday' => $this->holiday,
            'overday' => $this->overday,
            'approved_by_id' => $this->approved_by_id,
            'approved_by' => $this->whenLoaded('approvedBy', fn () => new EmployeeResource($this->approvedBy)),
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
