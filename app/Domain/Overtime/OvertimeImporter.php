<?php

namespace App\Domain\Overtime;

use App\Models\Attendance\Overtime;
use App\Models\Employee\Employee;

class OvertimeImporter
{
    public function import(iterable $overtimes): void
    {
        foreach ($overtimes as $overtime) {
            if (! (isset($overtime['employee_code']) || isset($overtime['date']))) {
                continue;
            }

            /** @var Employee|null $employee */
            $employee = Employee::query()
                ->where('code', strtoupper(trim((string) $overtime['employee_code'])))
                ->first();

            if (! $employee) {
                continue;
            }

            $overtimeDate = \DateTime::createFromFormat(
                (string) config('hris.format.date'),
                trim((string) $overtime['date'])
            );

            /** @var Overtime $object */
            $object = Overtime::query()
                ->where('employee_id', $employee->getKey())
                ->whereDate('overtime_date', $overtimeDate->format('Y-m-d'))
                ->first();

            if (! $object) {
                $object = new Overtime();
                $object->overtime_date = $overtimeDate;
                $object->employee_id = $employee->getKey();
            }

            $hasCheckIn = isset($overtime['check_in']) && $overtime['check_in'];
            $hasCheckOut = isset($overtime['check_out']) && $overtime['check_out'];

            if (! $hasCheckIn || ! $hasCheckOut) {
                $object->start_hour = '00:00:00';
                $object->end_hour = '00:00:00';
            } else {
                $object->start_hour = \DateTime::createFromFormat('H:i', trim((string) $overtime['check_in']))->format('H:i:s');
                $object->end_hour = \DateTime::createFromFormat('H:i', trim((string) $overtime['check_out']))->format('H:i:s');
            }

            $object->save();
        }
    }
}