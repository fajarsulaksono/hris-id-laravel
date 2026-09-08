<?php

namespace App\Domain\Overtime;

use App\Models\Attendance\Overtime;

class OvertimeCalculator extends Calculator implements OvertimeCalculatorInterface
{
    /** @var OvertimeCalculatorInterface[] */
    private array $calculators;

    public function __construct(array $calculators = [])
    {
        $this->calculators = [];

        foreach ($calculators as $calculator) {
            $this->addCalculator($calculator);
        }
    }

    public function calculate(Overtime $overtime): void
    {
        foreach ($this->calculators as $calculator) {
            try {
                $calculator->setWorkdayPerWeek($this->workday);

                if (isset($this->curve)) {
                    $calculator->setCurve($this->curve);
                }

                $calculator->calculate($overtime);
            } catch (CalculatorException $exception) {
                continue;
            }
        }
    }

    private function addCalculator(OvertimeCalculatorInterface $calculator): void
    {
        $this->calculators[] = $calculator;
    }
}