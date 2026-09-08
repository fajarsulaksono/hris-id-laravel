<?php

namespace App\Domain\Salary;

use App\Models\Employee\Employee;
use App\Models\Payroll\Payroll;

/**
 * Port dari SemartHris\Component\Salary\Processor\SalaryProcessorInterface:
 * processor dalam chain semarthris.salary_processor (lembur, BPJS, ...).
 */
interface SalaryProcessorInterface
{
    public function process(Payroll $payroll, Employee $employee, \DateTimeInterface $date, float $fixedSalary): float;
}