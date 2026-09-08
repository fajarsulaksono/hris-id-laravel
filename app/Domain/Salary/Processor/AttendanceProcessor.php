<?php

namespace App\Domain\Salary\Processor;

use App\Domain\Attendance\AttendanceSummaryCalculator;
use App\Domain\Salary\PayrollProcessorInterface;
use App\Models\Employee\Employee;

/**
 * Port dari SemartHris\Component\Salary\Processor\AttendanceProcessor:
 * menghitung rekap kehadiran karyawan untuk periode payroll sebelum gaji dihitung.
 */
class AttendanceProcessor implements PayrollProcessorInterface
{
    public function __construct(private AttendanceSummaryCalculator $attendanceSummaryCalculator)
    {
    }

    public function process(Employee $employee, \DateTimeInterface $date): void
    {
        $this->attendanceSummaryCalculator->calculate($employee, $date);
    }
}