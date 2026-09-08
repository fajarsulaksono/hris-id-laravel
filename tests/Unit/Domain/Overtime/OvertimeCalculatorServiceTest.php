<?php

namespace Tests\Unit\Domain\Overtime;

use App\Domain\Overtime\OvertimeCalculatorService;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Shiftment;
use App\Models\Attendance\Workshift;
use App\Models\Employee\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvertimeCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_valid_overtime_from_workshift(): void
    {
        [$employee, $shiftment] = $this->scenario('EMP001');

        $overtime = new Overtime();
        $overtime->employee_id = $employee->getKey();
        $overtime->overtime_date = new \DateTime('2026-01-05');
        $overtime->start_hour = '18:00:00';
        $overtime->end_hour = '20:00:00';

        app(OvertimeCalculatorService::class)->calculate($overtime);

        $this->assertSame($shiftment->getKey(), $overtime->shiftment_id);
        $this->assertSame(2.0, $overtime->raw_value);
        $this->assertSame(3.5, $overtime->calculated_value);
        $this->assertNotSame(config('hris.overtime.invalid_message'), $overtime->description);
    }

    public function test_it_strips_processing_mark_from_description(): void
    {
        [$employee] = $this->scenario('EMP002');

        $overtime = new Overtime();
        $overtime->employee_id = $employee->getKey();
        $overtime->overtime_date = new \DateTime('2026-01-05');
        $overtime->start_hour = '18:00:00';
        $overtime->end_hour = '20:00:00';
        $overtime->description = 'PROCESSED#LAPORAN PROYEK';

        app(OvertimeCalculatorService::class)->calculate($overtime);

        $this->assertSame('LAPORAN PROYEK', $overtime->description);
    }

    public function test_it_marks_invalid_without_workshift(): void
    {
        [$employee] = $this->scenario('EMP003');

        // Pindahkan workshift agar tidak mencakup 2026-02-01.
        Workshift::where('employee_id', $employee->getKey())->delete();

        $overtime = new Overtime();
        $overtime->employee_id = $employee->getKey();
        $overtime->overtime_date = new \DateTime('2026-01-05');
        $overtime->start_hour = '18:00:00';
        $overtime->end_hour = '20:00:00';
        $overtime->holiday = true;

        app(OvertimeCalculatorService::class)->calculate($overtime);

        $this->assertSame(config('hris.overtime.invalid_message'), $overtime->description);
        $this->assertSame(0.0, $overtime->raw_value);
        $this->assertSame(0.0, $overtime->calculated_value);
        $this->assertNull($overtime->approved_by_id);
        $this->assertFalse($overtime->holiday);
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

        return [$employee, $shiftment];
    }
}