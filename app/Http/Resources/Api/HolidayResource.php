<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'holiday_date' => $this->holiday_date?->format('Y-m-d'),
            'name' => $this->name,
            'created_at' => $this->created_at,
        ];
    }
}
