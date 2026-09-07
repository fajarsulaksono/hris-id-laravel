<?php

namespace App\Domain\Overtime;

use App\Models\Attendance\Overtime;

class HolidayCalculator extends Calculator
{
    public function calculate(Overtime $overtime): void
    {
        if (!$overtime->holiday) {
            throw new CalculatorException();
        }

        if (5 === $this->workday) {
            $overtime->calculated_value = $this->calculateFiveDay($overtime);
        } else {
            $overtime->calculated_value = $this->calculateSixDay($overtime);
        }
    }

    private function calculateFiveDay(Overtime $overtime): float
    {
        $hours = $this->getOvertimeHours($overtime);
        $overtime->raw_value = $hours;

        if (8 < $hours) {
            $calculatedValue = 8 * 2;
            $hours -= 8;
        } else {
            $calculatedValue = $hours * 2;
            $hours -= $hours;
        }

        if (0 < $hours) {
            --$hours;
            if (0 < $hours) {
                $calculatedValue += (3 * 1);
                $calculatedValue += (4 * $hours);
            } else {
                $calculatedValue += (3 * $hours);
            }
        }

        return $calculatedValue;
    }

    private function calculateSixDay(Overtime $overtime): float
    {
        $hours = $this->getOvertimeHours($overtime);
        $overtime->raw_value = $hours;

        if (5 === $overtime->overtime_date->format('N')) {
            if (5 < $hours) {
                $calculatedValue = 5 * 2;
                $hours -= 5;
            } else {
                $calculatedValue = $hours * 2;
                $hours -= $hours;
            }
        } else {
            if (7 < $hours) {
                $calculatedValue = 7 * 2;
                $hours -= 7;
            } else {
                $calculatedValue = $hours * 2;
                $hours -= $hours;
            }
        }

        if (0 < $hours) {
            --$hours;
            if (0 < $hours) {
                $calculatedValue += (3 * 1);
                $calculatedValue += (4 * $hours);
            } else {
                $calculatedValue += (3 * $hours);
            }
        }

        return $calculatedValue;
    }
}