<?php

namespace Tests\Unit\Domain\Attendance;

use App\Domain\Attendance\WorkshiftFinder;
use App\Domain\Attendance\WorkshiftSlicer;
use App\Models\Attendance\Shiftment;
use App\Models\Attendance\Workshift;
use App\Models\Employee\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkshiftSlicerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_splits_overlapping_workshifts_into_three_periods(): void
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

        $shiftment = Shiftment::create([
            'code' => 'SH1',
            'name' => 'SHIFT 1',
            'start_hour' => '08:00:00',
            'end_hour' => '17:00:00',
        ]);

        $first = Workshift::create([
            'employee_id' => $employee->getKey(),
            'shiftment_id' => $shiftment->getKey(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]);

        $second = new Workshift([
            'employee_id' => $employee->getKey(),
            'shiftment_id' => $shiftment->getKey(),
            'start_date' => '2026-01-10',
            'end_date' => '2026-01-20',
        ]);

        app(WorkshiftSlicer::class)->slice($first, $second);

        $this->assertSame('2026-01-09', $first->end_date->format('Y-m-d'));

        $third = app(WorkshiftFinder::class)->findIntersection($second);

        $this->assertNull($third);

        /** @var Workshift $sliced */
        $sliced = Workshift::where('description', 'SLICED BY SYSTEM')->first();

        $this->assertNotNull($sliced);
        $this->assertSame('2026-01-21', $sliced->start_date->format('Y-m-d'));
        $this->assertSame('2026-01-31', $sliced->end_date->format('Y-m-d'));
        $this->assertSame($shiftment->getKey(), $sliced->shiftment_id);
    }

    public function test_it_ignores_different_employees(): void
    {
        $employeeA = Employee::create([
            'code' => 'EMP001',
            'full_name' => 'Budi Santoso',
            'username' => 'budi.santoso',
            'email' => 'budi.santoso@example.test',
            'password' => 'password123',
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '3174012509900001',
        ]);

        $employeeB = Employee::create([
            'code' => 'EMP002',
            'full_name' => 'Sari Wulandari',
            'username' => 'sari.wulandari',
            'email' => 'sari.wulandari@example.test',
            'password' => 'password123',
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '3174012509900002',
        ]);

        $first = new Workshift([
            'employee_id' => $employeeA->getKey(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]);

        $second = new Workshift([
            'employee_id' => $employeeB->getKey(),
            'start_date' => '2026-01-10',
            'end_date' => '2026-01-20',
        ]);

        app(WorkshiftSlicer::class)->slice($first, $second);

        $this->assertSame('2026-01-31', $first->end_date->format('Y-m-d'));
    }
}