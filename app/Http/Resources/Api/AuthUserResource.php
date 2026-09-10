<?php

namespace App\Http\Resources\Api;

use App\Support\Security;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'username' => $this->username,
            'company_id' => $this->company_id,
            'company_name' => $this->company_name,
            'department_id' => $this->department_id,
            'department_name' => $this->department_name,
            'job_level_id' => $this->job_level_id,
            'job_level_name' => $this->job_level_name,
            'job_title_id' => $this->job_title_id,
            'job_title_name' => $this->job_title_name,
            'roles' => $this->getRoleNames(),
            'abilities' => collect(array_keys(config('hris.abilities', [])))
                ->filter(fn (string $ability) => app(Security::class)->can($this->resource, $ability))
                ->values()
                ->all(),
            'profile_photo' => $this->profile_photo_url,
            'created_at' => $this->created_at,
        ];
    }
}
