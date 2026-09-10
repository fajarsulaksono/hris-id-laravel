<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'letter_number' => $this->letter_number,
            'subject' => $this->subject,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'signed_date' => $this->signed_date?->format('Y-m-d'),
            'tags' => $this->tags,
            'used' => $this->used,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
