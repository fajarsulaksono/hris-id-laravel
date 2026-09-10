<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'skill_group_id' => $this->skill_group_id,
            'skill_group' => $this->whenLoaded('skillGroup', fn () => new SkillGroupResource($this->skillGroup)),
            'created_at' => $this->created_at,
        ];
    }
}
