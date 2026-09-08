<?php

namespace Tests\Unit\Domain\Attendance;

use App\Domain\Attendance\HolidayChecker;
use App\Domain\Attendance\WorkdayCalculator;
use App\Models\Master\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkdayCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_counts_workdays_excluding_weekends(): void
    {
        $calculator = new WorkdayCalculator(new HolidayChecker('6,7'));

        $this->assertSame(22, $calculator->getWorkdays(new \DateTime('2026-01-01')));
    }

    public function test_it_excludes_national_holidays(): void
    {
        Holiday::create([
            'holiday_date' => '2026-01-05',
            'name' => 'Cuti Bersama',
        ]);

        $calculator = new WorkdayCalculator(new HolidayChecker('6,7'));

        $this->assertSame(21, $calculator->getWorkdays(new \DateTime('2026-01-01')));
    }

    public function test_it_honors_date_limit_and_start(): void
    {
        $calculator = new WorkdayCalculator(new HolidayChecker('6,7'));

        // 1-10 Januari 2026: Senin-Jumat = 1,2,5,6,7,8,9 (Sabtu 3 & Minggu 4 libur) = 7 hari.
        $this->assertSame(7, $calculator->getWorkdays(new \DateTime('2026-01-01'), 10));
    }
}