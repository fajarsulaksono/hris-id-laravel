<?php

namespace App\Domain\Attendance;

use App\Models\Master\Holiday;

class HolidayChecker
{
    /** @var int[] */
    private array $offDayPerWeek;

    public function __construct(string|array $offDayPerWeek)
    {
        $offDays = is_array($offDayPerWeek) ? $offDayPerWeek : explode(',', $offDayPerWeek);

        $this->offDayPerWeek = array_map('intval', array_filter($offDays, static fn ($day) => $day !== ''));
    }

    public function isHoliday(\DateTimeInterface $date): bool
    {
        if ($this->isWeekendHoliday($date)) {
            return true;
        }

        return Holiday::query()
            ->whereDate('holiday_date', $date->format('Y-m-d'))
            ->exists();
    }

    public function isWeekendHoliday(\DateTimeInterface $date): bool
    {
        return in_array((int) $date->format('N'), $this->offDayPerWeek, true);
    }

    public function getOffDayPerWeek(): array
    {
        return $this->offDayPerWeek;
    }
}