<?php

namespace App\Domain\Overtime;

use App\Models\Attendance\Overtime;

class WorkdayCalculator extends Calculator
{
    public function calculate(Overtime $overtime): void
    {
        if ($overtime->holiday) {
            throw new CalculatorException();
        }

        $hours = $this->getOvertimeHours($overtime);
        $overtime->raw_value = $hours;
        //1 first hour multiply with 1.5
        $calculatedValue = 1.5 * 1;
        --$hours;
        if (0 < $hours) {
            $calculatedValue += (2 * $hours); //Others, multiply with 2
        }

        $overtime->calculated_value = $calculatedValue;
    }
}