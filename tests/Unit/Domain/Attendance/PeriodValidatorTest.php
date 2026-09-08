<?php

namespace Tests\Unit\Domain\Attendance;

use App\Domain\Attendance\InvalidAttendancePeriodException;
use App\Domain\Attendance\PeriodValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodValidatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_period_in_the_future(): void
    {
        $future = (int) now()->addMonth()->format('n');

        $this->expectException(InvalidAttendancePeriodException::class);

        app(PeriodValidator::class)->validate((int) now()->addMonth()->format('Y'), $future);
    }

    public function test_it_rejects_period_on_or_before_closed_through(): void
    {
        config(['hris.attendance.closed_through' => '2026-06']);

        $this->expectException(InvalidAttendancePeriodException::class);

        app(PeriodValidator::class)->validate(2026, 6);
    }

    public function test_it_allows_period_after_closed_through(): void
    {
        config(['hris.attendance.closed_through' => '2026-06']);

        $period = app(PeriodValidator::class)->validate(2026, 7);

        $this->assertSame('2026-07-01', $period->format('Y-m-d'));
    }
}