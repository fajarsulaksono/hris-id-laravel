<?php

namespace App\Domain\Attendance;

use Illuminate\Support\Carbon;

class PeriodValidator
{
    public function validate(int $year, int $month): \DateTimeInterface
    {
        $period = Carbon::create($year, $month, 1)->startOfMonth();

        if ($period->isAfter(Carbon::now()->startOfMonth())) {
            throw new InvalidAttendancePeriodException($period);
        }

        $closedThrough = config('hris.attendance.closed_through');

        if ($closedThrough) {
            $closedAt = Carbon::parse($closedThrough)->startOfMonth();

            if ($period->lte($closedAt)) {
                throw new InvalidAttendancePeriodException($period);
            }
        }

        return $period;
    }
}