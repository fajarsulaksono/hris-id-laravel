<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'region_id' => $this->region_id,
            'region' => $this->whenLoaded('region', fn () => new RegionResource($this->region)),
            'created_at' => $this->created_at,
        ];
    }
}
