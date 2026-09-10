<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxGroupHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn () => new EmployeeResource($this->employee)),
            'old_tax_group' => $this->old_tax_group?->value,
            'new_tax_group' => $this->new_tax_group?->value,
            'old_risk_ratio' => $this->old_risk_ratio?->value,
            'new_risk_ratio' => $this->new_risk_ratio?->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
