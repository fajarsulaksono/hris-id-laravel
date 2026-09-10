<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'birth_day' => $this->birth_day?->format('Y-m-d'),
            'email' => $this->email,
            'tax_number' => $this->tax_number,
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', fn () => new self($this->parent)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
