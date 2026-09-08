<?php

namespace App\Domain\Salary\Processor;

use App\Domain\Salary\SalaryProcessorInterface;
use App\Domain\Salary\Service\StoreAsCompanyCost;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Employee\Employee;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollDetail;
use App\Models\Payroll\SalaryComponent;

/**
 * Port dari SemartHris\Component\Salary\Processor\OvertimeProcessor:
 * tunjangan lembur = (1/173) * gaji tetap * total lembur, dibulatkan ke bawah.
 */
class OvertimeProcessor implements SalaryProcessorInterface
{
    public function __construct(
        private StoreAsCompanyCost $storeAsCompanyCost,
        private string $overtimeComponentCode,
    ) {
    }

    public function process(Payroll $payroll, Employee $employee, \DateTimeInterface $date, float $fixedSalary): float
    {
        $overtimeValue = 0.0;

        if (! $employee->have_overtime_benefit) {
            return $overtimeValue;
        }

        $overtimeComponent = SalaryComponent::query()->where('code', $this->overtimeComponentCode)->first();
        if (! $overtimeComponent) {
            throw new \RuntimeException('Overtime benefit code is not valid.');
        }

        $summary = AttendanceSummary::query()
            ->where('employee_id', $employee->getKey())
            ->where('year', (int) $date->format('Y'))
            ->where('month', (int) $date->format('n'))
            ->first();

        if (! $summary) {
            return $overtimeValue;
        }

        $overtimeValue = round((1 / 173) * $fixedSalary * $summary->total_overtime, 0, PHP_ROUND_HALF_DOWN);

        $payrollDetail = PayrollDetail::firstOrNew([
            'payroll_id' => $payroll->getKey(),
            'component_id' => $overtimeComponent->getKey(),
        ]);
        $payrollDetail->benefit_value = (string) $overtimeValue;

        $this->storeAsCompanyCost->store($payrollDetail);
        $payrollDetail->save();

        return $overtimeValue;
    }
}