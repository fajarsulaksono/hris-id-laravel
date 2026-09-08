<?php

namespace App\Domain\Salary\Service;

use App\Domain\Salary\PayrollProcessorInterface;
use App\Domain\Salary\Processor\InvalidPayrollPeriodException;
use App\Models\Employee\Employee;
use App\Models\Payroll\PayrollPeriod;

/**
 * Port dari SemartHris\Component\Salary\Service\PayrollProcessor:
 * validasi periode payroll (periode sebelumnya harus sudah diproses,
 * periode tidak boleh ditutup) lalu menjalankan chain payroll.
 */
class PayrollProcessor
{
    public function __construct(
        private PayrollProcessorInterface $payrollProcessor,
    ) {
    }

    public function process(Employee $employee, \DateTimeInterface $date): void
    {
        $this->validate($employee, $date);
        $this->payrollProcessor->process($employee, $date);
    }

    private function validate(Employee $employee, \DateTimeInterface $date): void
    {
        $prevPeriod = (clone $date)->modify('-1 month');

        $prev = $this->findPeriod($employee, $prevPeriod);
        if (! $prev && ! $this->isEmptyForDate($date)) {
            throw new \InvalidArgumentException('Previous period must be processed before processing it period.');
        }

        $period = $this->findPeriod($employee, $date);
        if (! $period) {
            $period = PayrollPeriod::create([
                'company_id' => $employee->company_id,
                'year' => (int) $date->format('Y'),
                'month' => (int) $date->format('n'),
                'closed' => false,
            ]);
        }

        if ($prev && $prev->month !== $period->month - 1) {
            throw new \InvalidArgumentException('Previous period must be processed before processing it period.');
        }

        if ($period->closed) {
            throw new InvalidPayrollPeriodException($date);
        }

        PayrollPeriod::query()
            ->whereKeyNot($period->getKey())
            ->where('closed', false)
            ->update(['closed' => true]);
    }

    private function findPeriod(Employee $employee, \DateTimeInterface $date): ?PayrollPeriod
    {
        return PayrollPeriod::query()
            ->where('company_id', $employee->company_id)
            ->where('year', (int) $date->format('Y'))
            ->where('month', (int) $date->format('n'))
            ->first();
    }

    /**
     * Port PayrollPeriodRepository::isEmptyOrNotEqueal: tidak ada periode pada
     * tahun-bulan lain (selain yang sedang diproses).
     */
    private function isEmptyForDate(\DateTimeInterface $date): bool
    {
        return ! PayrollPeriod::query()
            ->where('year', '!=', (int) $date->format('Y'))
            ->where('month', '!=', (int) $date->format('n'))
            ->exists();
    }
}