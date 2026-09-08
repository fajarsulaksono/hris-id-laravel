<?php

namespace Tests\Feature;

use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Attendance\Leave;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Workshift;
use App\Models\Employee\Employee;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\SalaryAllowance;
use App\Models\Payroll\SalaryBenefit;
use App\Models\Tax\Tax;
use Database\Seeders\DummyDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DummyDataSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'hris.seed_dummy.employees' => 4,
            'hris.seed_dummy.months' => 2,
        ]);
    }

    public function test_seeds_full_cycle_for_two_companies(): void
    {
        $this->seed(DummyDataSeeder::class);

        $this->assertSame(4, Employee::where('code', 'like', 'E0%')->count());

        foreach (Employee::where('code', 'like', 'E0%')->get() as $employee) {
            $this->assertNotNull($employee->address_id, "{$employee->code} must have a default address");
            $this->assertSame(1, Workshift::where('employee_id', $employee->getKey())->count());
            $this->assertTrue($employee->careerHistories()->exists(), "{$employee->code} must have career history");
            $this->assertGreaterThanOrEqual(2, SalaryBenefit::where('employee_id', $employee->getKey())->count());
        }

        $employees = Employee::where('code', 'like', 'E0%')->pluck('id');
        $months = collect(config('hris.seed_dummy.months'));

        $periods = PayrollPeriod::all();
        $this->assertSame(2, $periods->pluck('company_id')->unique()->count(), 'dummy covers two companies');
        $this->assertTrue($periods->isNotEmpty());
        $this->assertTrue($periods->every(fn (PayrollPeriod $period) => $period->closed), 'every payroll period must be closed');

        $this->assertTrue(Attendance::whereIn('employee_id', $employees)->exists(), 'attendance must be present');
        $this->assertTrue(AttendanceSummary::whereIn('employee_id', $employees)->exists(), 'attendance summary must be present');
        $this->assertTrue(Overtime::exists(), 'overtime must be present');
        $this->assertTrue(Leave::exists(), 'leave must be present');
        $this->assertTrue(SalaryAllowance::whereIn('employee_id', $employees)->exists(), 'monthly allowances must be present');

        $this->assertTrue(Payroll::whereIn('employee_id', $employees)->exists(), 'payroll rows must be present');

        $payroll = Payroll::with('employee')->whereIn('employee_id', $employees)->first();
        $this->assertGreaterThan(0, (int) $payroll->take_home_pay, 'take home pay must be a positive number');

        $this->assertTrue(Tax::whereIn('employee_id', $employees)->exists(), 'tax rows must be present');
    }

    public function test_seeding_twice_does_not_duplicate_data(): void
    {
        $this->seed(DummyDataSeeder::class);

        $attendanceCount = Attendance::count();
        $payrollCount = Payroll::count();
        $periodCount = PayrollPeriod::count();
        $summaryCount = AttendanceSummary::count();

        $this->seed(DummyDataSeeder::class);

        $this->assertSame(4, Employee::where('code', 'like', 'E0%')->count());
        $this->assertSame($attendanceCount, Attendance::count());
        $this->assertSame($payrollCount, Payroll::count());
        $this->assertSame($periodCount, PayrollPeriod::count());
        $this->assertSame($summaryCount, AttendanceSummary::count());
    }
}
