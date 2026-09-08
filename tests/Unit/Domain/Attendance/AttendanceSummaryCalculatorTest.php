<?php

namespace Tests\Unit\Domain\Attendance;

use App\Domain\Attendance\AttendanceSummaryCalculator;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Shiftment;
use App\Models\Attendance\Workshift;
use App\Models\Employee\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceSummaryCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_monthly_summary(): void
    {
        $employee = $this->employee('EMP001', true);
        $user = $this->employee('EMP002', false);

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

        Attendance::create([
            'employee_id' => $employee->getKey(),
            'attendance_date' => '2026-01-05',
            'check_in' => '08:00:00',
            'check_out' => '21:00:00',
            'absent' => false,
        ]);

        Overtime::create([
            'employee_id' => $employee->getKey(),
            'overtime_date' => '2026-01-05',
            'start_hour' => '18:00:00',
            'end_hour' => '20:00:00',
            'approved_by_id' => $user->getKey(),
        ]);

        app(AttendanceSummaryCalculator::class)->calculate($employee, new \DateTime('2026-01-10'));

        $summary = AttendanceSummary::where('employee_id', $employee->getKey())
            ->where('year', 2026)
            ->where('month', 1)
            ->first();

        $this->assertNotNull($summary);
        $this->assertSame(22, $summary->total_workday);
        $this->assertSame(1, $summary->total_in);
        $this->assertSame(21, $summary->total_absent);
        $this->assertSame(240, $summary->total_loyality); // late_out 21:00 - 17:00 = 240 menit
        $this->assertSame(3, $summary->total_overtime);   // 2 jam lembur kerja -> kolom integer
    }

    public function test_it_updates_existing_summary_instead_of_duplicating(): void
    {
        $employee = $this->employee('EMP003');

        AttendanceSummary::create([
            'employee_id' => $employee->getKey(),
            'year' => 2026,
            'month' => 1,
            'total_workday' => 22,
            'total_in' => 0,
            'total_loyality' => 0,
            'total_absent' => 22,
            'total_overtime' => 0,
        ]);

        app(AttendanceSummaryCalculator::class)->calculate($employee, new \DateTime('2026-01-10'));

        $this->assertSame(1, AttendanceSummary::count());
    }

    private function employee(string $code, bool $benefit = false): Employee
    {
        return Employee::create([
            'code' => $code,
            'full_name' => 'Budi Santoso',
            'username' => strtolower($code),
            'email' => strtolower($code).'@example.test',
            'password' => 'password123',
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '317401250990'.substr($code, -3),
            'have_overtime_benefit' => $benefit,
        ]);
    }
}