<?php

namespace Tests\Unit\Domain\Overtime;

use App\Domain\Overtime\HolidayCalculator;
use App\Domain\Overtime\OvertimeCalculator;
use App\Domain\Overtime\WorkdayCalculator;
use App\Models\Attendance\Overtime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvertimeCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_workday_calculation_multiples_first_hour_by_one_point_five(): void
    {
        $overtime = new Overtime();
        $overtime->overtime_date = new \DateTime('2026-01-05');
        $overtime->start_hour = '17:00:00';
        $overtime->end_hour = '20:00:00';
        $overtime->holiday = false;

        $calculator = new WorkdayCalculator();
        $calculator->setWorkdayPerWeek(5);
        $calculator->calculate($overtime);

        $this->assertSame(3.0, $overtime->raw_value);
        $this->assertSame(5.5, $overtime->calculated_value); // 1.5 + (2 * 2)
        $this->assertFalse($overtime->overday);
    }

    public function test_overday_is_flagged_when_end_passes_midnight(): void
    {
        $overtime = new Overtime();
        $overtime->overtime_date = new \DateTime('2026-01-05');
        $overtime->start_hour = '22:00:00';
        $overtime->end_hour = '01:00:00';
        $overtime->holiday = false;

        $calculator = new WorkdayCalculator();
        $calculator->setWorkdayPerWeek(5);
        $calculator->calculate($overtime);

        $this->assertTrue($overtime->overday);
        $this->assertSame(3.0, $overtime->raw_value); // 3 jam (22:00-01:00)
    }

    public function test_holiday_calculation_with_five_workday_week(): void
    {
        $overtime = new Overtime();
        $overtime->overtime_date = new \DateTime('2026-01-03'); // Sabtu
        $overtime->start_hour = '08:00:00';
        $overtime->end_hour = '11:00:00';
        $overtime->holiday = true;

        $calculator = new HolidayCalculator();
        $calculator->setWorkdayPerWeek(5);
        $calculator->calculate($overtime);

        $this->assertSame(3.0, $overtime->raw_value);
        $this->assertSame(6.0, $overtime->calculated_value); // 3 * 2
    }

    public function test_chain_skips_calculators_that_do_not_apply(): void
    {
        $overtime = new Overtime();
        $overtime->overtime_date = new \DateTime('2026-01-05');
        $overtime->start_hour = '17:00:00';
        $overtime->end_hour = '20:00:00';
        $overtime->holiday = false;

        $chain = new OvertimeCalculator([new WorkdayCalculator(), new HolidayCalculator()]);
        $chain->setWorkdayPerWeek(5);
        $chain->calculate($overtime);

        $this->assertSame(5.5, $overtime->calculated_value);
        $this->assertSame(3.0, $overtime->raw_value);
    }
}