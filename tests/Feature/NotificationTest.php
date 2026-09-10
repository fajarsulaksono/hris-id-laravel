<?php

namespace Tests\Feature;

use App\Enums\RiskRatio;
use App\Enums\TaxGroup;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Shiftment;
use App\Models\Attendance\Workshift;
use App\Models\Company\Company;
use App\Models\Employee\Employee;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\SalaryBenefit;
use App\Models\Payroll\SalaryComponent;
use App\Notifications\OvertimeApprovedNotification;
use App\Notifications\PayrollProcessedNotification;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SalaryComponentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_auto_approved_overtime_sends_email_to_employee(): void
    {
        Notification::fake();

        $employee = $this->employee();
        $supervisor = $this->employee('SUPERVISOR');
        $supervisor->assignRole('HRSUPERVISOR');

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

        $this->actingAs($supervisor);

        Overtime::create([
            'employee_id' => $employee->getKey(),
            'overtime_date' => '2026-01-05',
            'start_hour' => '18:00:00',
            'end_hour' => '20:00:00',
        ]);

        Notification::assertSentTo($employee, OvertimeApprovedNotification::class);
    }

    public function test_payroll_process_sends_email_notification_to_employee(): void
    {
        Notification::fake();

        $this->seed(SalaryComponentSeeder::class);

        $company = Company::create([
            'code' => 'NOT',
            'name' => 'PT NOTIFICATION',
            'birth_day' => '2020-01-01',
            'email' => 'notif@example.test',
            'tax_number' => '01.001.002.3-004.000',
        ]);

        $employee = Employee::create([
            'company_id' => $company->getKey(),
            'code' => 'EMP001',
            'full_name' => 'Uji Notif',
            'username' => 'uji.notif',
            'email' => 'uji.notif@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2020-02-02',
            'date_of_birth' => '1991-03-03',
            'identity_number' => '3174010303910001',
            'tax_group' => TaxGroup::TK2,
            'risk_ratio' => RiskRatio::RISK_VERY_LOW,
            'have_overtime_benefit' => true,
        ]);

        $supervisor = $this->employee('SUPERVISOR-MGR');
        $supervisor->assignRole('HRSUPERVISOR');

        $component = SalaryComponent::where('code', 'GP')->first();

        SalaryBenefit::create([
            'employee_id' => $employee->getKey(),
            'component_id' => $component->getKey(),
            'benefit_value' => 5_000_000,
        ]);

        AttendanceSummary::create([
            'employee_id' => $employee->getKey(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'total_workday' => 20,
            'total_in' => 20,
            'total_loyality' => 0,
            'total_absent' => 0,
            'total_overtime' => 0,
        ]);

        PayrollPeriod::create([
            'company_id' => $company->getKey(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'closed' => false,
        ]);

        $this->actingAs($supervisor)->post(route('admin.payroll.payrolls.process'), [
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'company_id' => $company->getKey(),
        ])->assertRedirect();

        Notification::assertSentTo($employee, PayrollProcessedNotification::class);
    }

    private function employee(string $suffix = 'BUDI'): Employee
    {
        $identity = '317401250990'.str_pad((string) (abs(crc32($suffix)) % 90 + 10), 2, '0', STR_PAD_LEFT);

        return Employee::create([
            'code' => $suffix,
            'full_name' => 'Budi Santoso',
            'username' => strtolower($suffix).'.santoso',
            'email' => strtolower($suffix).'.santoso@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => $identity,
            'have_overtime_benefit' => true,
        ]);
    }
}
