<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'start_hour' => $this->start_hour,
            'end_hour' => $this->end_hour,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
