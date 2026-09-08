<?php

namespace Tests\Unit\Domain\Attendance;

use App\Domain\Attendance\AttendanceProcessor;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Shiftment;
use App\Models\Attendance\Workshift;
use App\Models\Employee\Employee;
use App\Models\Master\Holiday;
use App\Models\Master\Reason;
use App\Enums\ReasonType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceProcessorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_backfills_absences_for_full_month_workdays_only(): void
    {
        $employee = $this->employee('EMP001');

        Holiday::create([
            'holiday_date' => '2026-01-19',
            'name' => 'Cuti Bersama',
        ]);

        $processor = app(AttendanceProcessor::class);
        $processor->process($employee, new \DateTime('2026-01-01'));

        // 22 hari kerja dikurangi 1 hari libur nasional = 21 baris absen.
        $absent = Attendance::where('employee_id', $employee->getKey())
            ->where('absent', true);

        $this->assertSame(21, $absent->count());
        $this->assertDatabaseMissing('attendances', [
            'employee_id' => $employee->getKey(),
            'attendance_date' => '2026-01-03', // Sabtu
        ]);
        $this->assertDatabaseMissing('attendances', [
            'employee_id' => $employee->getKey(),
            'attendance_date' => '2026-01-19', // libur nasional
        ]);
    }

    public function test_it_assigns_default_absent_reason(): void
    {
        $employee = $this->employee('EMP002');

        Reason::create([
            'type' => ReasonType::ABSENT,
            'code' => 'ABS',
            'name' => 'ALFA',
        ]);

        $processor = app(AttendanceProcessor::class);
        $processor->process($employee, new \DateTime('2026-01-01'));

        $attendance = Attendance::whereDate('attendance_date', '2026-01-05')
            ->where('employee_id', $employee->getKey())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertTrue($attendance->absent);
        $this->assertNotNull($attendance->reason_id);
    }

    public function test_it_keeps_existing_attendance_untouched(): void
    {
        $employee = $this->employee('EMP003');

        Attendance::create([
            'employee_id' => $employee->getKey(),
            'attendance_date' => '2026-01-05',
            'check_in' => '07:30:00',
            'check_out' => '18:00:00',
            'absent' => false,
        ]);

        $processor = app(AttendanceProcessor::class);
        $processor->process($employee, new \DateTime('2026-01-01'));

        $attendance = Attendance::whereDate('attendance_date', '2026-01-05')
            ->where('employee_id', $employee->getKey())
            ->first();

        $this->assertFalse($attendance->absent);
        $this->assertSame('07:30:00', $attendance->check_in);
        $this->assertSame('18:00:00', $attendance->check_out);
    }

    public function test_it_links_workshift_shiftment_to_backfilled_record(): void
    {
        $employee = $this->employee('EMP004');

        $shiftment = Shiftment::create([
            'code' => 'SH1',
            'name' => 'SHIFT 1',
            'start_hour' => '08:00:00',
            'end_hour' => '17:00:00',
        ]);

        Workshift::create([
            'employee_id' => $employee->getKey(),
            'shiftment_id' => $shiftment->getKey(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]);

        $processor = app(AttendanceProcessor::class);
        $processor->process($employee, new \DateTime('2026-01-01'));

        $attendance = Attendance::whereDate('attendance_date', '2026-01-05')
            ->where('employee_id', $employee->getKey())
            ->first();

        $this->assertNotEmpty($attendance->shiftment_id);
    }

    private function employee(string $code): Employee
    {
        return Employee::create([
            'code' => $code,
            'full_name' => 'Budi Santoso',
            'username' => strtolower($code),
            'email' => strtolower($code).'@example.test',
            'password' => 'password123',
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '3174012509900001',
        ]);
    }
}