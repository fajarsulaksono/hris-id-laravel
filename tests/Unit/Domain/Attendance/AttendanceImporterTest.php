<?php

namespace Tests\Unit\Domain\Attendance;

use App\Domain\Attendance\AttendanceImporter;
use App\Models\Attendance\Attendance;
use App\Models\Employee\Employee;
use App\Models\Master\Reason;
use App\Enums\ReasonType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_present_and_absent_rows(): void
    {
        $employee = $this->employee('EMP001');

        Reason::create([
            'type' => ReasonType::ABSENT,
            'code' => 'ABS',
            'name' => 'ALFA',
        ]);

        (new AttendanceImporter())->import([
            ['employee_code' => 'emp001', 'date' => '05-01-2026', 'check_in' => '07:30', 'check_out' => '18:00', 'reason_code' => ''],
            ['employee_code' => 'EMP001', 'date' => '06-01-2026', 'check_in' => '', 'check_out' => '', 'reason_code' => 'ABS'],
        ]);

        $present = Attendance::whereDate('attendance_date', '2026-01-05')
            ->where('employee_id', $employee->getKey())
            ->first();

        $this->assertNotNull($present);
        $this->assertSame('2026-01-05', $present->attendance_date->format('Y-m-d'));
        $this->assertFalse($present->absent);
        $this->assertSame('07:30:00', $present->check_in);
        $this->assertSame('18:00:00', $present->check_out);

        $absent = Attendance::whereDate('attendance_date', '2026-01-06')
            ->where('employee_id', $employee->getKey())
            ->first();

        $this->assertNotNull($absent);
        $this->assertTrue($absent->absent);
        $this->assertNull($absent->check_in);
        $this->assertNotNull($absent->reason_id);
    }

    public function test_it_updates_existing_attendance_on_reimport(): void
    {
        $employee = $this->employee('EMP002');

        (new AttendanceImporter())->import([
            ['employee_code' => 'EMP002', 'date' => '05-01-2026', 'check_in' => '07:30', 'check_out' => '18:00', 'reason_code' => ''],
        ]);
        (new AttendanceImporter())->import([
            ['employee_code' => 'EMP002', 'date' => '05-01-2026', 'check_in' => '08:00', 'check_out' => '17:00', 'reason_code' => ''],
        ]);

        $attendance = Attendance::whereDate('attendance_date', '2026-01-05')
            ->where('employee_id', $employee->getKey())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertSame('08:00:00', $attendance->check_in);
        $this->assertSame('17:00:00', $attendance->check_out);
    }

    public function test_it_skips_rows_with_unknown_employee(): void
    {
        $employee = $this->employee('EMP003');

        (new AttendanceImporter())->import([
            ['employee_code' => 'EMPTY', 'date' => '05-01-2026', 'check_in' => '08:00', 'check_out' => '17:00', 'reason_code' => ''],
        ]);

        $this->assertDatabaseCount('attendances', 0);
        $this->assertDatabaseMissing('attendances', ['employee_id' => $employee->getKey()]);
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