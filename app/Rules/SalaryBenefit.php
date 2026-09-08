<?php

namespace App\Rules;

use App\Domain\Salary\Service\ValidateBenefit;
use App\Models\Employee\Employee;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Port dari SemartHris\Validator\SalaryBenefitValidator:
 * tunjangan tetap tidak boleh ditambahkan ke karyawan yang sudah diproses payroll.
 */
class SalaryBenefit implements ValidationRule
{
    public function __construct(private readonly ?string $employeeId = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $employeeId = $this->employeeId ?? (string) $value;
        $employee = Employee::query()->find($employeeId);

        if ($employee && app(ValidateBenefit::class)->employeeHasPayroll($employee)) {
            $fail('Karyawan sudah diproses payroll, tunjangan tidak dapat ditambahkan.');
        }
    }
}