<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'full_name' => $this->full_name,
            'employee_status' => $this->employee_status?->value,
            'employee_status_label' => $this->employee_status?->label(),
            'gender' => $this->gender?->value,
            'gender_label' => $this->gender?->label(),
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'identity_number' => $this->identity_number,
            'identity_type' => $this->identity_type?->value,
            'marital_status' => $this->marital_status?->value,
            'marital_status_label' => $this->marital_status?->label(),
            'email' => $this->email,
            'join_date' => $this->join_date?->format('Y-m-d'),
            'resign_date' => $this->resign_date?->format('Y-m-d'),
            'leave_balance' => $this->leave_balance,
            'tax_group' => $this->tax_group?->value,
            'risk_ratio' => $this->risk_ratio?->value,
            'have_overtime_benefit' => $this->have_overtime_benefit,
            'supervisor_id' => $this->supervisor_id,
            'supervisor' => $this->whenLoaded('supervisor', fn () => new self($this->supervisor)),
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company', fn () => new CompanyResource($this->company)),
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', fn () => new DepartmentResource($this->department)),
            'job_level_id' => $this->job_level_id,
            'job_level' => $this->whenLoaded('jobLevel', fn () => new JobLevelResource($this->jobLevel)),
            'job_title_id' => $this->job_title_id,
            'job_title' => $this->whenLoaded('jobTitle', fn () => new JobTitleResource($this->jobTitle)),
            'contract_id' => $this->contract_id,
            'contract' => $this->whenLoaded('contract', fn () => new ContractResource($this->contract)),
            'profile_photo' => $this->profile_photo_url,
            'roles' => $this->getRoleNames(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
