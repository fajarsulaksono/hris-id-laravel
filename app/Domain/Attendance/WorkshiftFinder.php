<?php

namespace App\Domain\Attendance;

use App\Models\Attendance\Workshift;

class WorkshiftFinder
{
    public function findByEmployeeAndDate(string $employeeId, \DateTimeInterface $date): ?Workshift
    {
        return Workshift::query()
            ->where('employee_id', $employeeId)
            ->whereDate('start_date', '<=', $date->format('Y-m-d'))
            ->whereDate('end_date', '>=', $date->format('Y-m-d'))
            ->first();
    }

    public function findIntersection(Workshift $workshift): ?Workshift
    {
        return Workshift::query()
            ->where('employee_id', $workshift->employee_id)
            ->whereDate('start_date', '<', $workshift->start_date->format('Y-m-d'))
            ->whereDate('end_date', '>', $workshift->end_date->format('Y-m-d'))
            ->first();
    }
}