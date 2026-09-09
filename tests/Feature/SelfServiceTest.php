<?php

namespace Tests\Feature;

use App\Enums\ReasonType;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Leave;
use App\Models\Employee\Employee;
use App\Models\Master\Reason;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollPeriod;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SelfServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_employee_can_view_own_profile_and_attendance(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        Attendance::create([
            'employee_id' => $employee->getKey(),
            'attendance_date' => now()->toDateString(),
            'check_in' => '08:00:00',
            'check_out' => '17:00:00',
            'absent' => false,
        ]);

        $this->actingAs($employee)
            ->get(route('my.profile'))
            ->assertOk()
            ->assertSee($employee->full_name);

        $this->actingAs($employee)
            ->get(route('my.attendance'))
            ->assertOk()
            ->assertSee('HADIR');
    }

    public function test_employee_can_submit_leave_and_see_history(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $reason = Reason::create(['type' => ReasonType::LEAVE, 'code' => 'CTH', 'name' => 'CUTI TAHUNAN']);

        $this->actingAs($employee)
            ->post(route('my.leaves.store'), [
                'leave_date' => now()->addDay()->toDateString(),
                'reason_id' => $reason->getKey(),
                'amount' => 2,
                'description' => 'Liburan keluarga',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, Leave::where('employee_id', $employee->getKey())->count());

        $this->actingAs($employee)
            ->get(route('my.leaves'))
            ->assertOk()
            ->assertSee('Liburan keluarga');
    }

    public function test_employee_cannot_download_other_employee_payslip(): void
    {
        $owner = $this->employee('budi.santoso', 'EMPLOYEE');
        $other = $this->employee('sari.wulandari', 'HRSTAFF');

        $payroll = Payroll::create([
            'employee_id' => $owner->getKey(),
            'period_id' => $this->newPeriod(),
            'take_home_pay' => 5000000,
        ]);

        $this->actingAs($other)
            ->get(route('my.payrolls.pdf', $payroll))
            ->assertForbidden();
    }

    public function test_employee_attendance_page_does_not_show_others_data(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $other = $this->employee('sari.wulandari', 'HRSTAFF');

        Attendance::create([
            'employee_id' => $other->getKey(),
            'attendance_date' => now()->toDateString(),
            'check_in' => '08:00:00',
            'check_out' => '17:00:00',
            'absent' => false,
        ]);

        $this->actingAs($employee)
            ->get(route('my.attendance'))
            ->assertOk()
            ->assertDontSee('SARI WULANDARI');
    }

    public function test_employee_can_list_own_payrolls(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        Payroll::create([
            'employee_id' => $employee->getKey(),
            'period_id' => $this->newPeriod(),
            'take_home_pay' => 7500000,
        ]);

        $this->actingAs($employee)
            ->get(route('my.payrolls'))
            ->assertOk()
            ->assertSee('7.500.000');
    }

    private function newPeriod(): string
    {
        return (string) PayrollPeriod::create([
            'company_id' => null,
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'closed' => false,
        ])->getKey();
    }

    private function employee(string $username, string $role): Employee
    {
        $employee = Employee::create([
            'code' => 'EMP-'.Str::upper(Str::random(6)),
            'full_name' => Str::of($username)->replace('.', ' ')->upper()->toString(),
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => Hash::make('password'),
            'join_date' => '2022-01-01',
            'employee_status' => 'p',
            'gender' => 'm',
            'date_of_birth' => '1990-01-01',
            'identity_number' => (string) rand(1000000000000000, 9999999999999999),
            'marital_status' => 's',
            'leave_balance' => 12,
        ]);

        $employee->assignRole($role);

        return $employee;
    }
}
