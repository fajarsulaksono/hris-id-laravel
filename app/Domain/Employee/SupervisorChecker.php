<?php

namespace App\Domain\Employee;

use App\Models\Employee\Employee;

class SupervisorChecker
{
    public function isAllowToSupervise(Employee $employee, Employee $supervisor): bool
    {
        $employeeJobTitle = $employee->jobTitle;
        $supervisorJobTitle = $supervisor->jobTitle;

        if (!$employeeJobTitle || !$supervisorJobTitle) {
            return false;
        }

        if ($employeeJobTitle->job_level_id === $supervisorJobTitle->job_level_id) {
            return false;
        }

        return $this->canSupervise($employee, $supervisor);
    }

    private function canSupervise(Employee $employee, Employee $supervisor): bool
    {
        if ($employeeSupervisor = $employee->supervisor) {
            if ($employeeSupervisor->id === $supervisor->id) {
                return true;
            }

            return $this->canSupervise($employeeSupervisor, $supervisor);
        }

        return false;
    }
}