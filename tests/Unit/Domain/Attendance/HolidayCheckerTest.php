<?php

namespace Tests\Unit\Domain\Attendance;

use App\Domain\Attendance\HolidayChecker;
use App\Models\Master\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_flags_weekend_days(): void
    {
        $checker = new HolidayChecker('6,7');

        $this->assertTrue($checker->isWeekendHoliday(new \DateTime('2026-01-03'))); // Sabtu
        $this->assertTrue($checker->isWeekendHoliday(new \DateTime('2026-01-04'))); // Minggu
        $this->assertFalse($checker->isWeekendHoliday(new \DateTime('2026-01-05'))); // Senin
    }

    public function test_it_flags_registered_national_holiday(): void
    {
        Holiday::create([
            'holiday_date' => '2026-08-17',
            'name' => 'Hari Kemerdekaan',
        ]);

        $checker = new HolidayChecker('6,7');

        $this->assertTrue($checker->isHoliday(new \DateTime('2026-08-17')));
        $this->assertFalse($checker->isHoliday(new \DateTime('2026-08-18')));
    }

    public function test_it_accepts_array_offdays(): void
    {
        $checker = new HolidayChecker([6, 7]);

        $this->assertSame([6, 7], $checker->getOffDayPerWeek());
    }
}