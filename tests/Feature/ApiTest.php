<?php

namespace Tests\Feature;

use App\Enums\ReasonType;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Leave;
use App\Models\Attendance\Overtime;
use App\Models\Company\Company;
use App\Models\Company\JobLevel;
use App\Models\Company\JobTitle;
use App\Models\Employee\Employee;
use App\Models\Master\Holiday;
use App\Models\Master\Reason;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_login_returns_token_and_user(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        $this->postJson('/api/v1/auth/login', [
            'username' => 'budi.santoso',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'full_name', 'username', 'roles']]);
    }

    public function test_login_rejects_bad_credentials(): void
    {
        $this->employee('budi.santoso', 'EMPLOYEE');

        $this->postJson('/api/v1/auth/login', [
            'username' => 'budi.santoso',
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }

    public function test_me_returns_authenticated_employee(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.username', 'budi.santoso');
    }

    public function test_logout_revokes_current_token(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_unauthenticated_api_request_returns_json_401(): void
    {
        $this->getJson('/api/v1/employees')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_employee_role_cannot_read_payroll(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/payroll-periods')
            ->assertForbidden()
            ->assertJson(['message' => 'Forbidden.']);
    }

    public function test_hr_supervisor_can_read_payroll_periods(): void
    {
        $supervisor = $this->employee('dewi.lestari', 'HRSUPERVISOR');
        $token = $this->tokenFor($supervisor);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/payroll-periods')
            ->assertOk();
    }

    public function test_search_filters_employees_by_q(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $token = $this->tokenFor($staff);
        $this->employee('budi.santoso', 'EMPLOYEE');

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/employees?q=budi')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.full_name', 'Budi Santoso');
    }

    public function test_pagination_respects_ep(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $token = $this->tokenFor($staff);

        for ($i = 0; $i < 5; $i++) {
            $this->employee('karyawan.'.$i, 'EMPLOYEE');
        }

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/employees?ep=2')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 6);
    }

    public function test_non_super_admin_only_sees_own_company_employees(): void
    {
        $companyA = Company::create(['code' => 'PT-A', 'name' => 'PT A', 'birth_day' => '1990-01-01', 'email' => 'a@test.id', 'tax_number' => '00.000.000.0-000.000']);
        $companyB = Company::create(['code' => 'PT-B', 'name' => 'PT B', 'birth_day' => '1991-01-01', 'email' => 'b@test.id', 'tax_number' => '00.000.000.0-000.000']);

        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $staff->update(['company_id' => $companyA->getKey()]);
        $this->employee('milik.b', 'EMPLOYEE')->update(['company_id' => $companyB->getKey()]);

        $token = $this->tokenFor($staff->fresh());

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/employees')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.full_name', 'Sari Wulandari');
    }

    public function test_super_admin_sees_all_company_employees(): void
    {
        $companyA = Company::create(['code' => 'PT-A', 'name' => 'PT A', 'birth_day' => '1990-01-01', 'email' => 'a@test.id', 'tax_number' => '00.000.000.0-000.000']);
        $companyB = Company::create(['code' => 'PT-B', 'name' => 'PT B', 'birth_day' => '1991-01-01', 'email' => 'b@test.id', 'tax_number' => '00.000.000.0-000.000']);

        $admin = $this->employee('agus.setiawan', 'SUPER_ADMIN');
        $this->employee('milik.a', 'EMPLOYEE')->update(['company_id' => $companyA->getKey()]);
        $this->employee('milik.b', 'EMPLOYEE')->update(['company_id' => $companyB->getKey()]);

        $token = $this->tokenFor($admin);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/employees')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);
    }

    public function test_hr_staff_can_create_and_delete_holiday(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $token = $this->tokenFor($staff);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/holidays', [
                'holiday_date' => '2026-12-25',
                'name' => 'Natal',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Natal');

        $holiday = Holiday::firstOrFail();

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->deleteJson("/api/v1/holidays/{$holiday->getKey()}")
            ->assertOk();

        $this->assertSoftDeleted('holidays', ['id' => $holiday->getKey()]);
    }

    public function test_read_only_module_rejects_store(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $token = $this->tokenFor($staff);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/departments', ['code' => 'IT', 'name' => 'IT'])
            ->assertForbidden();
    }

    public function test_hr_staff_can_update_job_title(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $level = JobLevel::create(['code' => 'MGR', 'name' => 'Manager']);
        $title = JobTitle::create([
            'code' => 'MGR',
            'name' => 'Manager',
            'job_level_id' => $level->getKey(),
        ]);
        $token = $this->tokenFor($staff);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->putJson("/api/v1/job-titles/{$title->getKey()}", [
                'code' => 'MGR',
                'name' => 'Senior Manager',
                'job_level_id' => $level->getKey(),
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'SENIOR MANAGER');
    }

    public function test_employee_can_clock_in_self_attendance(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/attendances', [
                'attendance_date' => '2026-09-01',
                'check_in' => '08:00',
            ])
            ->assertCreated()
            ->assertJsonPath('data.employee_id', $employee->getKey())
            ->assertJsonPath('data.check_in', '08:00');
    }

    public function test_employee_cannot_clock_in_twice_on_same_day(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $token = $this->tokenFor($employee);

        $payload = ['attendance_date' => '2026-09-01', 'check_in' => '08:00'];

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/attendances', $payload)
            ->assertCreated();

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/attendances', $payload)
            ->assertUnprocessable();
    }

    public function test_employee_clock_in_always_uses_token_employee(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $other = $this->employee('sinta.dewi', 'EMPLOYEE');
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/attendances', [
                'employee_id' => $other->getKey(),
                'attendance_date' => '2026-09-01',
                'check_in' => '08:00',
            ])
            ->assertCreated()
            ->assertJsonPath('data.employee_id', $employee->getKey());
    }

    public function test_employee_only_sees_own_attendances(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $other = $this->employee('sinta.dewi', 'EMPLOYEE');
        $token = $this->tokenFor($employee);

        Attendance::create(['employee_id' => $employee->getKey(), 'attendance_date' => '2026-09-01', 'check_in' => '08:00:00']);
        Attendance::create(['employee_id' => $other->getKey(), 'attendance_date' => '2026-09-02', 'check_in' => '08:00:00']);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/attendances')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.employee_id', $employee->getKey());
    }

    public function test_hr_staff_sees_all_company_attendances(): void
    {
        $company = Company::create(['code' => 'PT-A', 'name' => 'PT A', 'birth_day' => '1990-01-01', 'email' => 'a@test.id', 'tax_number' => '00.000.000.0-000.000']);

        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $staff->update(['company_id' => $company->getKey()]);
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $employee->update(['company_id' => $company->getKey()]);
        $other = $this->employee('sinta.dewi', 'EMPLOYEE');
        $other->update(['company_id' => $company->getKey()]);

        Attendance::create(['employee_id' => $employee->getKey(), 'attendance_date' => '2026-09-01', 'check_in' => '08:00:00']);
        Attendance::create(['employee_id' => $other->getKey(), 'attendance_date' => '2026-09-02', 'check_in' => '08:00:00']);

        $token = $this->tokenFor($staff->fresh());

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/attendances')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_employee_cannot_modify_other_attendance(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $other = $this->employee('sinta.dewi', 'EMPLOYEE');
        $attendance = Attendance::create(['employee_id' => $other->getKey(), 'attendance_date' => '2026-09-02', 'check_in' => '08:00:00']);
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->putJson("/api/v1/attendances/{$attendance->getKey()}", ['check_out' => '17:00:00'])
            ->assertNotFound();
    }

    public function test_employee_can_clock_out_own_attendance(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $attendance = Attendance::create(['employee_id' => $employee->getKey(), 'attendance_date' => '2026-09-01', 'check_in' => '08:00:00']);
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->putJson("/api/v1/attendances/{$attendance->getKey()}", ['check_out' => '17:00'])
            ->assertOk()
            ->assertJsonPath('data.check_out', '17:00');
    }

    public function test_employee_cannot_clock_out_twice(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $attendance = Attendance::create([
            'employee_id' => $employee->getKey(),
            'attendance_date' => '2026-09-01',
            'check_in' => '08:00:00',
            'check_out' => '17:00:00',
        ]);
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->putJson("/api/v1/attendances/{$attendance->getKey()}", ['check_out' => '18:00'])
            ->assertUnprocessable()
            ->assertJson(['message' => 'Check-out sudah dicatat.']);
    }

    public function test_employee_can_list_leave_reasons(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        Reason::create(['type' => ReasonType::LEAVE, 'code' => 'CTH', 'name' => 'CUTI TAHUNAN']);
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/reasons?type='.ReasonType::LEAVE->value)
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_employee_can_submit_own_leave(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $reason = Reason::create(['type' => ReasonType::LEAVE, 'code' => 'CTH', 'name' => 'CUTI TAHUNAN']);
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/leaves', [
                'leave_date' => '2026-09-15',
                'reason_id' => $reason->getKey(),
                'amount' => 2,
                'description' => 'Liburan keluarga',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.employee_id', $employee->getKey());
    }

    public function test_employee_cannot_list_other_employees_leaves(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $other = $this->employee('sinta.dewi', 'EMPLOYEE');
        Reason::create(['type' => ReasonType::LEAVE, 'code' => 'CTH', 'name' => 'CUTI TAHUNAN']);
        Leave::create(['employee_id' => $other->getKey(), 'leave_date' => '2026-09-10', 'reason_id' => Reason::first()->getKey(), 'amount' => 1]);
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/v1/leaves')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_employee_can_submit_own_overtime_with_auto_approved_status(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/overtimes', [
                'overtime_date' => '2026-09-15',
                'start_hour' => '18:00',
                'end_hour' => '20:00',
            ])
            ->assertCreated()
            ->assertJsonPath('data.employee_id', $employee->getKey())
            ->assertJsonPath('data.start_hour', '18:00');
    }

    public function test_employee_cannot_create_overtime_without_workshift(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $token = $this->tokenFor($employee);

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/v1/overtimes', [
                'overtime_date' => '2026-09-15',
                'start_hour' => '18:00',
                'end_hour' => '20:00',
            ])
            ->assertCreated();

        $overtime = Overtime::first();
        $this->assertNotNull($overtime);
        $this->assertFalse($overtime->holiday);
        $this->assertSame((int) $overtime->raw_value, 0);
    }

    private function employee(string $username, string $role): Employee
    {
        $sequence = ++$this->sequence;

        $employee = Employee::create([
            'code' => Str::upper(str_pad((string) $sequence, 3, '0', STR_PAD_LEFT)),
            'full_name' => Str::title(str_replace('.', ' ', $username)),
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '31740125099000'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
        ]);

        $employee->assignRole($role);

        return $employee;
    }

    private function tokenFor(Employee $employee): string
    {
        return $employee->createToken('test')->plainTextToken;
    }
}
