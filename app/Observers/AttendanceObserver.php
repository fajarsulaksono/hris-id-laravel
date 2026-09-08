<?php

namespace App\Observers;

use App\Domain\Attendance\AttendanceCalculator;
use App\Models\Attendance\Attendance;

class AttendanceObserver
{
    public function __construct(protected AttendanceCalculator $attendanceCalculator)
    {
    }

    public function creating(Attendance $attendance): void
    {
        $this->attendanceCalculator->calculate($attendance);
    }

    public function updating(Attendance $attendance): void
    {
        $this->attendanceCalculator->calculate($attendance);
    }
}