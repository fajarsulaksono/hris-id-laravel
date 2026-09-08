<?php

namespace Tests\Unit\Domain\Overtime;

use App\Domain\Overtime\OvertimeProcessor;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Shiftment;
use App\Models\Attendance\Workshift;
use App\Models\Employee\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvertimeProcessorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_recalculates_only_overtimes_within_period(): void
    {
        [$employee] = $this->scenario('EMP001');

        $inMonth = $this->rawOvertime($employee, '2026-01-05');
        $outMonth = $this->rawOvertime($employee, '2026-02-05');

        app(OvertimeProcessor::class)->process($employee, new \DateTime('2026-01-10'));

        // Proses bulanan memicu observer (holiday + auto-approve + recalc) sehingga
        // baris dalam periode terhitung dan marker PROCESSED# transien di-strip.
        $this->assertGreaterThan(0, (float) $inMonth->fresh()->raw_value);
        $this->assertGreaterThan(0, (float) $inMonth->fresh()->calculated_value);
        $this->assertStringNotContainsString('PROCESSED#', (string) $inMonth->fresh()->description);

        // Di luar periode tidak tersentuh sama sekali (tetap nilai mentah 0).
        $this->assertSame(0.0, (float) $outMonth->fresh()->raw_value);
        $this->assertSame(0.0, (float) $outMonth->fresh()->calculated_value);
        $this->assertStringNotContainsString('PROCESSED#', (string) $outMonth->fresh()->description);
    }

    public function test_it_does_nothing_when_no_overtime_exists(): void
    {
        [$employee] = $this->scenario('EMP002');

        app(OvertimeProcessor::class)->process($employee, new \DateTime('2026-01-10'));

        $this->assertSame(0, Overtime::where('employee_id', $employee->getKey())->count());
    }

    /**
     * Buat baris lembur mentah tanpa memicu observer kalkulator (setara hasil
     * upload yang belum diproses).
     */
    private function rawOvertime(Employee $employee, string $date): Overtime
    {
        $overtime = new Overtime([
            'employee_id' => $employee->getKey(),
            'overtime_date' => $date,
            'start_hour' => '18:00:00',
            'end_hour' => '20:00:00',
            'raw_value' => 0,
            'calculated_value' => 0,
        ]);

        $overtime->saveQuietly();

        return $overtime;
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