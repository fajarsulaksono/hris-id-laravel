<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period_id' => $this->period_id,
            'period' => $this->whenLoaded('period', fn () => new PayrollPeriodResource($this->period)),
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn () => new EmployeeResource($this->employee)),
            'tax_group' => $this->tax_group?->value,
            'untaxable' => $this->untaxable,
            'taxable' => $this->taxable,
            'tax_value' => $this->tax_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
