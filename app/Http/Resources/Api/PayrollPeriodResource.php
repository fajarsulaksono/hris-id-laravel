<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollPeriodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'year' => $this->year,
            'month' => $this->month,
            'display' => $this->display,
            'closed' => $this->closed,
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company', fn () => new CompanyResource($this->company)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
