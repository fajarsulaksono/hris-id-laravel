<?php

namespace App\Domain\Overtime;

use App\Models\Attendance\Overtime;

abstract class Calculator implements OvertimeCalculatorInterface
{
    protected int $workday;

    protected float $curve;

    public function setWorkdayPerWeek(int $workday): void
    {
        $this->workday = $workday;
    }

    public function setCurve(float $curve): void
    {
        $this->curve = $curve;
    }

    protected function getOvertimeHours(Overtime $overtime): float
    {
        $endHour = \DateTime::createFromFormat(
            config('hris.format.date_time'),
            sprintf('%s %s',
                date(config('hris.format.date')),
                $overtime->end_hour
            )
        );

        $startHour = \DateTime::createFromFormat(
            config('hris.format.date_time'),
            sprintf('%s %s',
                date(config('hris.format.date')),
                $overtime->start_hour
            )
        );

        if ($endHour < $startHour) {
            $endHour->add(new \DateInterval('P1D'));
            $overtime->overday = true;
        } else {
            $overtime->overday = false;
        }

        $delta = $endHour->diff($startHour, true);
        $hours = $delta->h;
        $minutes = $delta->i;
        if (15 < $minutes) {
            if (15 < $minutes && 45 >= $minutes) {
                $hours += 0.5;
            } else {
                ++$hours;
            }
        }

        return $this->breakSub((float) $hours);
    }

    private function breakSub(float $hours): float
    {
        $realHours = $hours;
        $flag = true;
        while ($flag) {
            if (4 <= $hours) {
                $realHours -= 0.5;
                $hours -= 4;

                $this->breakSub($hours);
            } else {
                $flag = false;
            }
        }

        return $realHours;
    }
}