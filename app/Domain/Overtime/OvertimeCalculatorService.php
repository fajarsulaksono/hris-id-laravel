<?php

namespace App\Domain\Overtime;

use App\Domain\Attendance\WorkshiftFinder;
use App\Models\Attendance\Overtime;

class OvertimeCalculatorService
{
    public function __construct(
        private OvertimeChecker $checker,
        private OvertimeCalculatorInterface $calculator,
        private WorkshiftFinder $workshiftFinder,
        private int $workdayPerWeek,
    ) {
    }

    public function calculate(Overtime $overtime): void
    {
        $overtime->description = trim((string) str_replace('PROCESSED#', '', (string) $overtime->description));

        if (! $this->checker->allowToOvertime($overtime)) {
            $this->setInvalid($overtime);

            return;
        }

        if (! $overtime->end_hour) {
            $this->setInvalid($overtime);

            return;
        }

        $workshift = $this->workshiftFinder->findByEmployeeAndDate(
            (string) $overtime->employee_id,
            $overtime->overtime_date
        );

        if (! $workshift) {
            $this->setInvalid($overtime);

            return;
        }

        $overtime->shiftment_id = $workshift->shiftment_id;

        $this->calculator->setWorkdayPerWeek($this->workdayPerWeek);
        $this->calculator->calculate($overtime);
    }

    private function setInvalid(Overtime $overtime): void
    {
        $overtime->description = (string) config('hris.overtime.invalid_message');
        $overtime->approved_by_id = null;
        $overtime->raw_value = 0.0;
        $overtime->calculated_value = 0.0;
        $overtime->holiday = false;
        $overtime->overday = false;
    }
}