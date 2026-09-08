<?php

namespace App\Domain\Attendance;

use App\Models\Attendance\Attendance;
use App\Models\Employee\Employee;

interface RuleInterface
{
    public function apply(Employee $employee, \DateTimeInterface $attendanceDate): Attendance;
}