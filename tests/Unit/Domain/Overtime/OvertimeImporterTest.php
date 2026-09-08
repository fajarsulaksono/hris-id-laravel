<?php

namespace Tests\Unit\Domain\Overtime;

use App\Domain\Overtime\OvertimeImporter;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Shiftment;
use App\Models\Attendance\Workshift;
use App\Models\Employee\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvertimeImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_rows_and_computes_hours(): void
    {
        [$employee] = $this->scenario('EMP001');

        (new OvertimeImporter())->import([
            ['employee_code' => 'EMP001', 'date' => '05-01-2026', 'check_in' => '18:00', 'check_out' => '20:00'],
        ]);

        $overtime = Overtime::whereDate('overtime_date', '2026-01-05')
            ->where('employee_id', $employee->getKey())
            ->first();

        $this->assertNotNull($overtime);
        $this->assertSame('18:00:00', $overtime->start_hour);
        $this->assertSame('20:00:00', $overtime->end_hour);
        $this->assertSame(2.0, $overtime->raw_value);
        $this->assertSame(3.5, $overtime->calculated_value);
    }

    public function test_it_skips_unknown_employee(): void
    {
        [$employee] = $this->scenario('EMP002');

        (new OvertimeImporter())->import([
            ['employee_code' => 'EMPTY', 'date' => '05-01-2026', 'check_in' => '18:00', 'check_out' => '20:00'],
        ]);

        $this->assertDatabaseMissing('overtimes', ['employee_id' => $employee->getKey()]);
    }

    /**
     * @return array{0: Employee}
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
            'have_overtime_benefit' => true,
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
        ]);

        Attendance::create([
            'employee_id' => $employee->getKey(),
            'attendance_date' => '2026-01-05',
            'check_in' => '08:00:00',
            'check_out' => '21:00:00',
            'absent' => false,
        ]);

        return [$employee];
    }
}