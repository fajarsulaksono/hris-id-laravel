<?php

namespace Tests\Unit\Domain\Attendance;

use App\Domain\Attendance\AttendanceCalculator;
use App\Domain\Attendance\WorkshiftFinder;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Shiftment;
use App\Models\Attendance\Workshift;
use App\Models\Employee\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_early_in_and_late_out_from_shift(): void
    {
        [$employee, $shiftment] = $this->scenario('EMP001');

        $attendance = new Attendance();
        $attendance->employee_id = $employee->getKey();
        $attendance->attendance_date = new \DateTime('2026-01-05');
        $attendance->check_in = '07:30';
        $attendance->check_out = '18:00';
        $attendance->late_in = -1;

        app(AttendanceCalculator::class)->calculate($attendance);

        $this->assertSame(30, $attendance->early_in);
        $this->assertSame(0, $attendance->late_in);
        $this->assertSame(60, $attendance->late_out);
        $this->assertSame(0, $attendance->early_out);
        $this->assertSame($shiftment->getKey(), $attendance->shiftment_id);
    }

    public function test_it_calculates_late_in_and_early_out(): void
    {
        [$employee] = $this->scenario('EMP002');

        $attendance = new Attendance();
        $attendance->employee_id = $employee->getKey();
        $attendance->attendance_date = new \DateTime('2026-01-05');
        $attendance->check_in = '08:15';
        $attendance->check_out = '16:30';
        $attendance->late_in = -1;

        app(AttendanceCalculator::class)->calculate($attendance);

        $this->assertSame(0, $attendance->early_in);
        $this->assertSame(15, $attendance->late_in);
        $this->assertSame(30, $attendance->early_out);
        $this->assertSame(0, $attendance->late_out);
    }

    public function test_it_zeros_times_for_absent_attendance(): void
    {
        [$employee] = $this->scenario('EMP003');

        $attendance = new Attendance();
        $attendance->employee_id = $employee->getKey();
        $attendance->attendance_date = new \DateTime('2026-01-05');
        $attendance->absent = true;
        $attendance->check_in = '07:30';
        $attendance->check_out = '18:00';
        $attendance->late_in = -1;

        app(AttendanceCalculator::class)->calculate($attendance);

        $this->assertNull($attendance->check_in);
        $this->assertNull($attendance->check_out);
        $this->assertSame(0, $attendance->early_in);
        $this->assertSame(0, $attendance->late_in);
        $this->assertSame(0, $attendance->late_out);
    }

    public function test_it_leaves_attendance_intact_without_workshift(): void
    {
        [$employee] = $this->scenario('EMP004');

        $attendance = new Attendance();
        $attendance->employee_id = $employee->getKey();
        $attendance->attendance_date = new \DateTime('2026-02-01');
        $attendance->check_in = '07:30';
        $attendance->check_out = '18:00';
        $attendance->late_in = -1;

        app(AttendanceCalculator::class)->calculate($attendance);

        $this->assertSame('07:30', $attendance->check_in);
        $this->assertSame('18:00', $attendance->check_out);
        $this->assertSame(0, $attendance->late_in);
        $this->assertNull($attendance->shiftment_id);
    }

    /**
     * @return array{0: Employee, 1: Shiftment}
     */
    private function scenario(string $code): array
    {
        $employee = Employee::create([
            'code' => $code,
            'full_name' => 'Budi Santoso',
            'username' => strtolower($code),
            'email' => strtolower($code).'@example.test',
            'password' => 'password123',
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '3174012509900001',
        ]);

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
            'description' => 'DESKRIPSI',
        ]);

        return [$employee, $shiftment];
    }
}