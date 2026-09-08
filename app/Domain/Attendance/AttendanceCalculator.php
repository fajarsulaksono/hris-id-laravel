<?php

namespace App\Domain\Attendance;

use App\Models\Attendance\Attendance;
use App\Models\Attendance\Shiftment;

class AttendanceCalculator
{
    public function __construct(private WorkshiftFinder $workshiftFinder)
    {
    }

    public function calculate(Attendance $attendance): void
    {
        if (-1 === (int) $attendance->late_in) {
            $attendance->late_in = 0;
        }

        $workshift = $this->workshiftFinder->findByEmployeeAndDate(
            (string) $attendance->employee_id,
            $attendance->attendance_date
        );

        if (! $workshift) {
            return;
        }

        $shiftment = $workshift->shiftment;
        $attendance->shiftment_id = $shiftment?->id;

        if ($attendance->absent || ! ($attendance->check_out || $attendance->check_in)) {
            $attendance->check_in = null;
            $attendance->check_out = null;
            $attendance->early_in = 0;
            $attendance->early_out = 0;
            $attendance->late_in = 0;
            $attendance->late_out = 0;

            return;
        }

        $attendance->reason_id = null;

        $this->doCalculate($attendance, $shiftment);
    }

    private function doCalculate(Attendance $attendance, ?Shiftment $shiftment): void
    {
        if (! $shiftment) {
            return;
        }

        $startHour = $this->timeToCarbon((string) $shiftment->start_hour);
        $endHour = $this->timeToCarbon((string) $shiftment->end_hour);

        $checkIn = $attendance->check_in
            ? $this->timeToCarbon((string) $attendance->check_in)
            : $startHour;
        $attendance->check_in = $checkIn->format('H:i:s');

        $checkOut = $attendance->check_out
            ? $this->timeToCarbon((string) $attendance->check_out)
            : $endHour;
        $attendance->check_out = $checkOut->format('H:i:s');

        if ($checkIn <= $startHour) {
            $attendance->early_in = (int) round(($startHour->getTimestamp() - $checkIn->getTimestamp()) / 60);
            $attendance->late_in = 0;
        } else {
            $attendance->late_in = (int) round(($checkIn->getTimestamp() - $startHour->getTimestamp()) / 60);
            $attendance->early_in = 0;
        }

        if ($checkOut >= $endHour) {
            $attendance->late_out = (int) round(($checkOut->getTimestamp() - $endHour->getTimestamp()) / 60);
            $attendance->early_out = 0;
        } else {
            $attendance->early_out = (int) round(($endHour->getTimestamp() - $checkOut->getTimestamp()) / 60);
            $attendance->late_out = 0;
        }
    }

    private function timeToCarbon(string $time): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('H:i', substr($time, 0, 5));
    }
}