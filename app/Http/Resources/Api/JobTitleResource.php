<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobTitleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'job_level_id' => $this->job_level_id,
            'job_level' => $this->whenLoaded('jobLevel', fn () => new JobLevelResource($this->jobLevel)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
