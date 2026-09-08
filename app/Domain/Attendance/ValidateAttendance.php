<?php

namespace App\Domain\Attendance;

use App\Models\Attendance\Attendance;

class ValidateAttendance
{
    public static function validate(Attendance $attendance): bool
    {
        if ($attendance->absent && null === $attendance->reason_id) {
            return false;
        }

        if (! $attendance->absent && (null === $attendance->check_in || null === $attendance->check_out)) {
            return false;
        }

        return true;
    }
}