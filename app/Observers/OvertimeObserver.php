<?php

namespace App\Observers;

use App\Domain\Attendance\HolidayChecker;
use App\Domain\Overtime\OvertimeCalculatorService;
use App\Models\Attendance\Overtime;
use App\Models\Employee\Employee;
use App\Notifications\OvertimeApprovedNotification;

class OvertimeObserver
{
    public function __construct(
        protected OvertimeCalculatorService $overtimeCalculatorService,
        protected HolidayChecker $holidayChecker,
    ) {}

    public function creating(Overtime $overtime): void
    {
        $this->calculate($overtime);
    }

    public function updating(Overtime $overtime): void
    {
        $this->calculate($overtime);
    }

    public function created(Overtime $overtime): void
    {
        $this->notifyApproved($overtime);
    }

    public function updated(Overtime $overtime): void
    {
        $this->notifyApproved($overtime);
    }

    private function calculate(Overtime $overtime): void
    {
        if (! $overtime->holiday && $this->holidayChecker->isHoliday($overtime->overtime_date)) {
            $overtime->holiday = true;
        }

        if (config('hris.overtime.auto_approved')) {
            $user = auth()->user();

            if ($user instanceof Employee) {
                $overtime->approved_by_id = $user->getKey();
            }
        }

        $this->overtimeCalculatorService->calculate($overtime);
    }

    private function notifyApproved(Overtime $overtime): void
    {
        if (! config('hris.overtime.auto_approved') || ! $overtime->approved_by_id || $overtime->employee_id === null) {
            return;
        }

        $overtime->employee?->notify(new OvertimeApprovedNotification($overtime));
    }
}
