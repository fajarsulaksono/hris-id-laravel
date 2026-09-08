<?php

namespace App\Domain\Attendance;

use App\Models\Attendance\Attendance;
use App\Models\Employee\Employee;
use App\Models\Master\Reason;

class AttendanceProcessor
{
    public const CUT_OFF_LAST_DATE = -1;

    public function __construct(
        private RuleInterface $attendanceRule,
        private HolidayChecker $holidayChecker,
        private WorkshiftFinder $workshiftFinder,
        private string $reasonCode,
        private int $cutOffDate,
    ) {
    }

    public function process(Employee $employee, \DateTimeInterface $date): void
    {
        if (self::CUT_OFF_LAST_DATE === $this->cutOffDate) {
            $this->processFullMonth($employee, $date);
        } else {
            $this->processPartialMonth($employee, $date, $this->cutOffDate);
        }
    }

    private function processFullMonth(Employee $employee, \DateTimeInterface $date): void
    {
        $count = (int) $date->format('t');

        for ($i = 1; $i <= $count; ++$i) {
            $attendanceDate = \DateTime::createFromFormat('Y-m-j', sprintf('%s-%d', $date->format('Y-m'), $i));
            $this->doProcess($employee, $attendanceDate);
        }
    }

    private function processPartialMonth(Employee $employee, \DateTimeInterface $date, int $cutOff): void
    {
        $countPrevMonth = (int) $date->modify('-1 month')->format('t');

        for ($i = ($cutOff + 1); $i <= $countPrevMonth; ++$i) {
            $attendanceDate = \DateTime::createFromFormat('Y-m-j', sprintf('%s-%d', $date->format('Y-m'), $i));
            $this->doProcess($employee, $attendanceDate);
        }

        for ($i = 1; $i <= $cutOff; ++$i) {
            $attendanceDate = \DateTime::createFromFormat('Y-m-j', sprintf('%s-%d', $date->format('Y-m'), $i));
            $this->doProcess($employee, $attendanceDate);
        }
    }

    private function doProcess(Employee $employee, \DateTimeInterface $date): void
    {
        $attendance = Attendance::query()
            ->where('employee_id', $employee->getKey())
            ->whereDate('attendance_date', $date->format('Y-m-d'))
            ->first();

        if (! $attendance) {
            try {
                $attendance = $this->attendanceRule->apply($employee, $date);
            } catch (NotQualifiedException $exception) {
                //Do nothing
            }
        }

        if ($this->holidayChecker->isHoliday($date) && ! $attendance) {
            return;
        }

        if (! $attendance) {
            $workshift = $this->workshiftFinder->findByEmployeeAndDate(
                (string) $employee->getKey(),
                $date
            );

            $reason = Reason::query()
                ->where('code', $this->reasonCode)
                ->first();

            $attendance = new Attendance();
            $attendance->employee_id = $employee->getKey();
            $attendance->attendance_date = $date->format('Y-m-d');
            $attendance->shiftment_id = $workshift?->shiftment_id;
            $attendance->reason_id = $reason?->getKey();
            $attendance->absent = true;
        }

        $attendance->late_in = -1;

        $attendance->save();
    }
}