<?php

namespace App\Domain\Attendance;

use App\Models\Attendance\Attendance;
use App\Models\Employee\Employee;

class AttendanceRule implements RuleInterface
{
    /** @var RuleInterface[] */
    private array $rules;

    public function __construct(array $rules = [])
    {
        $this->rules = [];

        foreach ($rules as $rule) {
            $this->addRule($rule);
        }
    }

    public function apply(Employee $employee, \DateTimeInterface $attendanceDate): Attendance
    {
        foreach ($this->rules as $rule) {
            try {
                return $rule->apply($employee, $attendanceDate);
            } catch (NotQualifiedException $exception) {
                continue;
            }
        }

        throw new NotQualifiedException();
    }

    private function addRule(RuleInterface $rule): void
    {
        $this->rules[] = $rule;
    }
}