<?php

namespace App\Domain\Attendance;

use App\Enums\ReasonType;
use App\Models\Attendance\Attendance;
use App\Models\Employee\Employee;
use App\Models\Master\Reason;
use App\Support\StringUtil;

class AttendanceImporter
{
    public function import(iterable $attendances): void
    {
        foreach ($attendances as $attendance) {
            if (! (isset($attendance['employee_code']) || isset($attendance['date']))) {
                continue;
            }

            $employee = Employee::query()
                ->where('code', StringUtil::uppercase(StringUtil::sanitize($attendance['employee_code'])))
                ->first();

            if (! $employee) {
                continue;
            }

            $attendanceDate = \DateTime::createFromFormat(
                (string) config('hris.format.date'),
                StringUtil::sanitize($attendance['date'])
            );

            /** @var Attendance $object */
            $object = Attendance::query()
                ->where('employee_id', $employee->getKey())
                ->whereDate('attendance_date', $attendanceDate->format('Y-m-d'))
                ->first();

            if (! $object) {
                $object = new Attendance();
                $object->attendance_date = $attendanceDate;
                $object->employee_id = $employee->getKey();
            }

            $object->late_in = -1;

            $hasCheckIn = isset($attendance['check_in']) && $attendance['check_in'];
            $hasCheckOut = isset($attendance['check_out']) && $attendance['check_out'];

            if (! $hasCheckIn || ! $hasCheckOut) {
                $object->absent = true;
                if (isset($attendance['reason_code']) && ! empty($attendance['reason_code'])) {
                    $reason = Reason::query()
                        ->where('code', StringUtil::uppercase(StringUtil::sanitize($attendance['reason_code'])))
                        ->where('type', ReasonType::ABSENT)
                        ->first();

                    if ($reason) {
                        $object->reason_id = $reason->getKey();
                    }
                }
            } else {
                $object->absent = false;
                $object->check_in = \DateTime::createFromFormat('H:i', StringUtil::sanitize($attendance['check_in']))->format('H:i:s');
                $object->check_out = \DateTime::createFromFormat('H:i', StringUtil::sanitize($attendance['check_out']))->format('H:i:s');
            }

            $object->save();
        }
    }
}