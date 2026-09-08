<?php

namespace App\Domain\Overtime;

use App\Models\Attendance\Overtime;
use App\Models\Employee\Employee;

class OvertimeProcessor
{
    public const CUT_OFF_LAST_DATE = -1;

    public const PROCESS_MARK = 'PROCESSED';

    public function __construct(private int $cutOffDate)
    {
    }

    public function process(Employee $employee, \DateTimeInterface $date): void
    {
        if (self::CUT_OFF_LAST_DATE === $this->cutOffDate) {
            $this->processFullMonth($employee, $date);
        } else {
            $this->processPartialMonth($employee, $date, $this->cutOffDate);
        }
    }

    private function processFullMonth(Employee $employee, \DateTimeInterface $date): void
    {
        $count = (int) $date->format('t');

        for ($i = 1; $i <= $count; ++$i) {
            $overtimeDate = \DateTime::createFromFormat('Y-m-j', sprintf('%s-%d', $date->format('Y-m'), $i));
            $this->doProcess($employee, $overtimeDate);
        }
    }

    private function processPartialMonth(Employee $employee, \DateTimeInterface $date, int $cutOff): void
    {
        $countPrevMonth = (int) $date->modify('-1 month')->format('t');

        for ($i = ($cutOff + 1); $i <= $countPrevMonth; ++$i) {
            $overtimeDate = \DateTime::createFromFormat('Y-m-j', sprintf('%s-%d', $date->format('Y-m'), $i));
            $this->doProcess($employee, $overtimeDate);
        }

        for ($i = 1; $i <= $cutOff; ++$i) {
            $overtimeDate = \DateTime::createFromFormat('Y-m-j', sprintf('%s-%d', $date->format('Y-m'), $i));
            $this->doProcess($employee, $overtimeDate);
        }
    }

    private function doProcess(Employee $employee, \DateTimeInterface $date): void
    {
        /** @var Overtime|null $overtime */
        $overtime = Overtime::query()
            ->where('employee_id', $employee->getKey())
            ->whereDate('overtime_date', $date->format('Y-m-d'))
            ->first();

        if (! $overtime) {
            return;
        }

        $overtime->description = sprintf('%s#%s', self::PROCESS_MARK, $overtime->description);

        // Simpan tanpa membangkitkan event observer agar penanda PROSES tidak ikut di-strip
        // oleh OvertimeCalculatorService saat kalkulasi ulang.
        $overtime->saveQuietly();
    }
}