<?php

namespace App\Domain\Attendance;

class WorkdayCalculator
{
    public function __construct(private HolidayChecker $holidayChecker)
    {
    }

    public function getWorkdays(\DateTimeInterface $month, int $dateLimit = 0, int $dateStart = 1): int
    {
        $workdays = 0;
        $totalDate = $dateLimit ?: (int) $month->format('t');

        for ($i = $dateStart; $i <= $totalDate; ++$i) {
            $date = \DateTime::createFromFormat('Y-m-j', sprintf('%s-%d', $month->format('Y-m'), $i));

            if ($this->holidayChecker->isHoliday($date)) {
                continue;
            }

            ++$workdays;
        }

        return $workdays;
    }
}