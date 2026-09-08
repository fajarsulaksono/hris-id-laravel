<?php

namespace App\Domain\Salary\Service;

use App\Models\Employee\Employee;
use App\Models\Payroll\Payroll;

/**
 * Port dari SemartHris\Component\Salary\Service\ValidateBenefit:
 * tunjangan tetap tidak boleh diubah/ditambah jika karyawan sudah diproses payroll.
 */
class ValidateBenefit
{
    public function employeeHasPayroll(Employee $employee): bool
    {
        return Payroll::query()->where('employee_id', $employee->getKey())->exists();
    }
}