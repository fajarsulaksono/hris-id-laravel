<?php

namespace App\Domain\Job;

use App\Models\Employee\CareerHistory;
use App\Models\Employee\Mutation;
use App\Models\Employee\Placement;

class CareerHistoryService
{
    public const PLACEMENT_DESCRIPTION = 'PENEMPATAN';

    /**
     * Catat riwayat karir dari penempatan atau mutasi karyawan.
     * Port dari SemartHris AddCareerHistory::store.
     */
    public function recordForPlacement(Placement $placement): ?CareerHistory
    {
        return $this->store(
            employeeId: $placement->employee_id,
            companyId: $placement->company_id,
            departmentId: $placement->department_id,
            jobLevelId: $placement->job_level_id,
            jobTitleId: $placement->job_title_id,
            supervisorId: $placement->supervisor_id,
            contractId: $placement->contract_id,
            description: self::PLACEMENT_DESCRIPTION,
        );
    }

    public function recordForMutation(Mutation $mutation): ?CareerHistory
    {
        $employee = $mutation->employee;

        if ($employee === null) {
            return null;
        }

        return $this->store(
            employeeId: $mutation->employee_id,
            companyId: $mutation->old_company_id ?? $employee->company_id,
            departmentId: $mutation->old_department_id ?? $employee->department_id,
            jobLevelId: $mutation->old_job_level_id ?? $employee->job_level_id,
            jobTitleId: $mutation->old_job_title_id ?? $employee->job_title_id,
            supervisorId: $mutation->old_supervisor_id ?? $employee->supervisor_id,
            contractId: $employee->contract_id,
            description: $mutation->type?->label() ?? '',
        );
    }

    private function store(
        ?string $employeeId,
        ?string $companyId,
        ?string $departmentId,
        ?string $jobLevelId,
        ?string $jobTitleId,
        ?string $supervisorId,
        ?string $contractId,
        string $description,
    ): ?CareerHistory {
        if (! $employeeId || ! $jobTitleId || ! $description) {
            return null;
        }

        return CareerHistory::firstOrCreate([
            'employee_id' => $employeeId,
            'company_id' => $companyId,
            'department_id' => $departmentId,
            'job_level_id' => $jobLevelId,
            'job_title_id' => $jobTitleId,
            'supervisor_id' => $supervisorId,
        ], [
            'contract_id' => $contractId,
            'description' => $description,
        ]);
    }
}