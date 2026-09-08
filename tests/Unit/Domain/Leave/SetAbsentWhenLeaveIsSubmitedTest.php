<?php

namespace Tests\Unit\Domain\Leave;

use App\Domain\Leave\SetAbsentWhenLeaveIsSubmited;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Leave;
use App\Models\Employee\Employee;
use App\Models\Master\Reason;
use App\Enums\ReasonType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetAbsentWhenLeaveIsSubmitedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_attendance_absent_for_each_leave_day(): void
    {
        $employee = Employee::create([
            'code' => 'EMP001',
            'full_name' => 'Budi Santoso',
            'username' => 'budi.santoso',
            'email' => 'budi.santoso@example.test',
            'password' => 'password123',
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '3174012509900001',
        ]);

        $reason = Reason::create([
            'type' => ReasonType::LEAVE,
            'code' => 'CT',
            'name' => 'CUTI TAHUNAN',
        ]);

        $leave = new Leave();
        $leave->employee_id = $employee->getKey();
        $leave->leave_date = new \DateTime('2026-01-05');
        $leave->reason_id = $reason->getKey();
        $leave->amount = 3;
        $leave->description = 'CUTI KELUARGA';

        (new SetAbsentWhenLeaveIsSubmited())->setAbsent($leave);

        $absent = Attendance::where('employee_id', $employee->getKey())
            ->where('absent', true)
            ->orderBy('attendance_date')
            ->get();

        $this->assertCount(3, $absent);
        $this->assertSame(
            ['2026-01-05', '2026-01-06', '2026-01-07'],
            $absent->map(fn (Attendance $attendance) => $attendance->attendance_date->format('Y-m-d'))->all()
        );
        $this->assertSame($reason->getKey(), $absent->first()->reason_id);
        $this->assertSame('CUTI KELUARGA', $absent->first()->description);
    }

    public function test_it_turns_existing_present_attendance_into_absent(): void
    {
        $employee = Employee::create([
            'code' => 'EMP002',
            'full_name' => 'Budi Santoso',
            'username' => 'budi.santoso',
            'email' => 'budi.santoso@example.test',
            'password' => 'password123',
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '3174012509900001',
        ]);

        $reason = Reason::create([
            'type' => ReasonType::LEAVE,
            'code' => 'CT',
            'name' => 'CUTI TAHUNAN',
        ]);

        $existing = Attendance::create([
            'employee_id' => $employee->getKey(),
            'attendance_date' => '2026-01-05',
            'check_in' => '08:00:00',
            'check_out' => '17:00:00',
            'absent' => false,
        ]);

        $leave = new Leave();
        $leave->employee_id = $employee->getKey();
        $leave->leave_date = new \DateTime('2026-01-05');
        $leave->reason_id = $reason->getKey();
        $leave->amount = 1;

        (new SetAbsentWhenLeaveIsSubmited())->setAbsent($leave);

        $existing->refresh();

        $this->assertTrue($existing->absent);
        $this->assertSame($reason->getKey(), $existing->reason_id);
    }
}