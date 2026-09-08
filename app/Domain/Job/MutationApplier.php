<?php

namespace App\Domain\Job;

use App\Models\Employee\Mutation;

class MutationApplier
{
    /**
     * Isi kolom old_* mutasi dari posisi karyawan saat ini.
     * Port dari SemartHris SetOldJobMutation.
     */
    public function fillOldJob(Mutation $mutation): void
    {
        $employee = $mutation->employee;

        if ($employee === null) {
            return;
        }

        $mutation->old_company_id ??= $employee->company_id;
        $mutation->old_department_id ??= $employee->department_id;
        $mutation->old_job_level_id ??= $employee->job_level_id;
        $mutation->old_job_title_id ??= $employee->job_title_id;
        $mutation->old_supervisor_id ??= $employee->supervisor_id;
    }

    /**
     * Terapkan posisi baru mutasi ke data karyawan.
     * Port dari SemartHris SetEmployeeNewJob.
     */
    public function applyNewJob(Mutation $mutation): void
    {
        $employee = $mutation->employee;

        if ($employee === null) {
            return;
        }

        $employee->company_id = $mutation->new_company_id ?? $employee->company_id;
        $employee->department_id = $mutation->new_department_id ?? $employee->department_id;
        $employee->job_level_id = $mutation->new_job_level_id ?? $employee->job_level_id;
        $employee->job_title_id = $mutation->new_job_title_id ?? $employee->job_title_id;
        $employee->supervisor_id = $mutation->new_supervisor_id ?? $employee->supervisor_id;

        $employee->save();
    }
}