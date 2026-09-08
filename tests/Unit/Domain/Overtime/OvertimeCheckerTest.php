<?php

namespace Tests\Unit\Domain\Overtime;

use App\Domain\Overtime\OvertimeChecker;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Overtime;
use App\Models\Employee\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvertimeCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_denies_employee_without_overtime_benefit(): void
    {
        $employee = $this->employee('EMP001', false);

        $overtime = new Overtime();
        $overtime->employee_id = $employee->getKey();
        $overtime->overtime_date = new \DateTime('2026-01-05');

        $this->assertFalse(app(OvertimeChecker::class)->allowToOvertime($overtime));
    }

    public function test_it_denies_when_no_attendance_on_that_date(): void
    {
        $employee = $this->employee('EMP002', true);

        $overtime = new Overtime();
        $overtime->employee_id = $employee->getKey();
        $overtime->overtime_date = new \DateTime('2026-01-05');

        $this->assertFalse(app(OvertimeChecker::class)->allowToOvertime($overtime));
    }

    public function test_it_denies_when_attendance_is_absent(): void
    {
        $employee = $this->employee('EMP003', true);

        Attendance::create([
            'employee_id' => $employee->getKey(),
            'attendance_date' => '2026-01-05',
            'absent' => true,
        ]);

        $overtime = new Overtime();
        $overtime->employee_id = $employee->getKey();
        $overtime->overtime_date = new \DateTime('2026-01-05');

        $this->assertFalse(app(OvertimeChecker::class)->allowToOvertime($overtime));
    }

    public function test_it_clamps_overtime_to_attendance_hours(): void
    {
        $employee = $this->employee('EMP004', true);

        Attendance::create([
            'employee_id' => $employee->getKey(),
            'attendance_date' => '2026-01-05',
            'check_in' => '08:00:00',
            'check_out' => '19:00:00',
            'absent' => false,
        ]);

        $overtime = new Overtime();
        $overtime->employee_id = $employee->getKey();
        $overtime->overtime_date = new \DateTime('2026-01-05');
        $overtime->start_hour = '18:00:00';
        $overtime->end_hour = '20:00:00';

        $this->assertTrue(app(OvertimeChecker::class)->allowToOvertime($overtime));
        $this->assertSame('18:00:00', $overtime->start_hour);
        $this->assertSame('19:00:00', $overtime->end_hour); // dipotong ke jam keluar absensi
    }

    private function employee(string $code, bool $benefit): Employee
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
            'have_overtime_benefit' => $benefit,
        ]);
    }
}