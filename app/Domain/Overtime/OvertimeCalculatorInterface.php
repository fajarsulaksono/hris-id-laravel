<?php

namespace App\Domain\Overtime;

use App\Models\Attendance\Overtime;

interface OvertimeCalculatorInterface
{
    public function calculate(Overtime $overtime): void;

    public function setWorkdayPerWeek(int $workday): void;

    public function setCurve(float $curve): void;
}