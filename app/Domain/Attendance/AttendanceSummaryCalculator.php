<?php

namespace App\Domain\Attendance;

use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Attendance\Overtime;
use App\Models\Employee\Employee;

class AttendanceSummaryCalculator
{
    public const CUT_OFF_LAST_DATE = -1;

    public function __construct(
        private WorkdayCalculator $workdayCalculator,
        private int $cutOffDate,
    ) {
    }

    public function calculate(Employee $employee, \DateTimeInterface $date): void
    {
        /** @var AttendanceSummary $summary */
        $summary = AttendanceSummary::query()
            ->where('employee_id', $employee->getKey())
            ->where('year', (int) $date->format('Y'))
            ->where('month', (int) $date->format('n'))
            ->first();

        if (! $summary) {
            $summary = new AttendanceSummary();
            $summary->employee_id = $employee->getKey();
            $summary->year = (int) $date->format('Y');
            $summary->month = (int) $date->format('n');
        }

        if (self::CUT_OFF_LAST_DATE === $this->cutOffDate) {
            $totalWorkday = $this->workdayCalculator->getWorkdays($date);
            $summary->total_workday = $totalWorkday;

            $from = \DateTime::createFromFormat('Y-m-d', sprintf('%s-01', $date->format('Y-m')));
            $to = \DateTime::createFromFormat('Y-m-d', sprintf('%s-%s', $date->format('Y-m'), $date->format('t')));

            $this->applyAttendanceSummary($summary, $from, $to);
        } else {
            $prev = (clone $date)->modify('-1 month');
            $preWorkday = $this->workdayCalculator->getWorkdays($prev, 0, ($this->cutOffDate + 1));
            $currWorkday = $this->workdayCalculator->getWorkdays($date, $this->cutOffDate);

            $summary->total_workday = $preWorkday + $currWorkday;

            $from = \DateTime::createFromFormat('Y-m-d', sprintf('%s-%s', $prev->format('Y-m'), ($this->cutOffDate + 1)));
            $to = \DateTime::createFromFormat('Y-m-d', sprintf('%s-%s', $date->format('Y-m'), $this->cutOffDate));

            $this->applyAttendanceSummary($summary, $from, $to);
        }

        $summary->save();
    }

    private function applyAttendanceSummary(AttendanceSummary $summary, \DateTimeInterface $from, \DateTimeInterface $to): void
    {
        $attendanceSummary = $this->getAttendanceSummaryByDate(
            (string) $summary->employee_id,
            $from,
            $to
        );

        $totalAbsent = $summary->total_workday - (int) $attendanceSummary['total_in'];
        $totalLoyality = ((int) $attendanceSummary['early_in'] - (int) $attendanceSummary['late_in'])
            + ((int) $attendanceSummary['late_out'] - (int) $attendanceSummary['early_out']);

        $summary->total_in = (int) ($attendanceSummary['total_in'] ?? 0);
        $summary->total_absent = $totalAbsent ?? 0;
        $summary->total_loyality = $totalLoyality ?? 0;

        $totalOvertime = $this->getOvertimeSummaryByDate(
            (string) $summary->employee_id,
            $from,
            $to
        );

        $summary->total_overtime = (int) ($totalOvertime['total_overtime'] ?? 0);
    }

    private function getAttendanceSummaryByDate(string $employeeId, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return Attendance::query()
            ->selectRaw('COUNT(1) as total_in')
            ->selectRaw('COALESCE(SUM(early_in), 0) as early_in')
            ->selectRaw('COALESCE(SUM(early_out), 0) as early_out')
            ->selectRaw('COALESCE(SUM(late_in), 0) as late_in')
            ->selectRaw('COALESCE(SUM(late_out), 0) as late_out')
            ->where('employee_id', $employeeId)
            ->where('absent', false)
            ->whereDate('attendance_date', '>=', $from->format('Y-m-d'))
            ->whereDate('attendance_date', '<=', $to->format('Y-m-d'))
            ->first()
            ?->toArray() ?? [];
    }

    private function getOvertimeSummaryByDate(string $employeeId, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return Overtime::query()
            ->selectRaw('COALESCE(SUM(calculated_value), 0) as total_overtime')
            ->whereNotNull('approved_by_id')
            ->where('employee_id', $employeeId)
            ->whereDate('overtime_date', '>=', $from->format('Y-m-d'))
            ->whereDate('overtime_date', '<=', $to->format('Y-m-d'))
            ->first()
            ?->toArray() ?? [];
    }
}