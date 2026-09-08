<?php

namespace App\Domain\Overtime;

use App\Models\Attendance\Attendance;
use App\Models\Attendance\Overtime;

class OvertimeChecker
{
    public function allowToOvertime(Overtime $overtime): bool
    {
        $employee = $overtime->employee;

        if (! $employee || ! $employee->have_overtime_benefit) {
            return false;
        }

        /** @var Attendance|null $attendance */
        $attendance = Attendance::query()
            ->where('employee_id', $employee->getKey())
            ->whereDate('attendance_date', $overtime->overtime_date->format('Y-m-d'))
            ->first();

        if (! $attendance) {
            return false;
        }

        if ($attendance->absent) {
            return false;
        }

        if ($attendance->check_in && $overtime->start_hour < $attendance->check_in) {
            $overtime->start_hour = $attendance->check_in;
        }

        if ($attendance->check_out && $overtime->end_hour > $attendance->check_out) {
            $overtime->end_hour = $attendance->check_out;
        }

        return true;
    }
}