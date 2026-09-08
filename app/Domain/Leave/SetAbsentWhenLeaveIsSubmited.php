<?php

namespace App\Domain\Leave;

use App\Models\Attendance\Attendance;
use App\Models\Attendance\Leave;

class SetAbsentWhenLeaveIsSubmited
{
    public function setAbsent(Leave $leave): void
    {
        $date = \DateTime::createFromFormat('Y-m-d', $leave->leave_date->format('Y-m-d'));
        $date->modify('-1 day');
        $amount = (int) $leave->amount;

        for ($i = 0; $i < $amount; ++$i) {
            $date->modify('+1 day');

            $attendance = Attendance::query()
                ->where('employee_id', $leave->employee_id)
                ->whereDate('attendance_date', $date->format('Y-m-d'))
                ->first();

            if (! $attendance) {
                $attendance = new Attendance();
                $attendance->employee_id = $leave->employee_id;
                $attendance->attendance_date = $date->format('Y-m-d');
            }

            $attendance->absent = true;
            $attendance->reason_id = $leave->reason_id;
            $attendance->description = $leave->description;

            $attendance->save();
        }
    }
}