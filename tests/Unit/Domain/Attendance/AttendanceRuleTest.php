<?php

namespace Tests\Unit\Domain\Attendance;

use App\Domain\Attendance\AttendanceRule;
use App\Domain\Attendance\NotQualifiedException;
use App\Domain\Attendance\RuleInterface;
use App\Models\Attendance\Attendance;
use App\Models\Employee\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_chain_throws_not_qualified(): void
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

        $this->expectException(NotQualifiedException::class);

        (new AttendanceRule([]))->apply($employee, new \DateTime('2026-01-05'));
    }

    public function test_chain_stops_at_first_matching_rule(): void
    {
        $employee = Employee::create([
            'code' => 'EMP002',
            'full_name' => 'Sari Wulandari',
            'username' => 'sari.wulandari',
            'email' => 'sari.wulandari@example.test',
            'password' => 'password123',
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '3174012509900002',
        ]);

        $attendance = new Attendance();
        $attendance->attendance_date = new \DateTime('2026-01-05');
        $attendance->absent = true;

        $first = new class implements RuleInterface {
            public function apply(Employee $employee, \DateTimeInterface $attendanceDate): Attendance
            {
                throw new NotQualifiedException();
            }
        };

        $second = new class ($attendance) implements RuleInterface {
            public function __construct(private Attendance $attendance)
            {
            }

            public function apply(Employee $employee, \DateTimeInterface $attendanceDate): Attendance
            {
                return $this->attendance;
            }
        };

        $rule = new AttendanceRule([$first, $second]);

        $this->assertSame($attendance, $rule->apply($employee, new \DateTime('2026-01-05')));
    }
}