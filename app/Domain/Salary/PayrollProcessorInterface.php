<?php

namespace App\Domain\Salary;

use App\Models\Employee\Employee;

/**
 * Port dari SemartHris\Component\Salary\Processor\PayrollProcessorInterface:
 * processor dalam chain semarthris.payroll_processor.
 */
interface PayrollProcessorInterface
{
    public function process(Employee $employee, \DateTimeInterface $date): void;
}