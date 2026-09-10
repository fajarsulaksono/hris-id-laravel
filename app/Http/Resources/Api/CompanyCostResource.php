<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyCostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payroll_id' => $this->payroll_id,
            'component_id' => $this->component_id,
            'component_name' => $this->component_name,
            'benefit_value' => $this->benefit_value,
            'created_at' => $this->created_at,
        ];
    }
}
