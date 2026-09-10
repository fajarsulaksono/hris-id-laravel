<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'state' => $this->state?->value,
            'state_label' => $this->state?->label(),
            'fixed' => $this->fixed,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
