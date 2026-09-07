<?php

namespace Tests\Feature;

use App\Models\Company\Company;
use App\Models\Employee\Employee;
use App\Models\Master\Holiday;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_gate_hierarchy_matches_documented_defaults(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $supervisor = $this->employee('dewi.lestari', 'HRSUPERVISOR');
        $superAdmin = $this->employee('agus.setiawan', 'SUPER_ADMIN');

        $this->actingAs($employee);
        $this->assertTrue(Gate::allows('view_personal'));
        $this->assertFalse(Gate::allows('view_master'));
        $this->assertFalse(Gate::allows('view_employee'));
        $this->assertFalse(Gate::allows('view_payroll'));
        $this->assertFalse(Gate::allows('view_config'));

        $this->actingAs($staff);
        $this->assertTrue(Gate::allows('view_master'));
        $this->assertTrue(Gate::allows('view_employee'));
        $this->assertTrue(Gate::allows('view_user'));
        $this->assertTrue(Gate::allows('view_address'));
        $this->assertFalse(Gate::allows('view_payroll'));
        $this->assertFalse(Gate::allows('view_config'));

        $this->actingAs($supervisor);
        $this->assertTrue(Gate::allows('view_payroll'));

        $this->actingAs($superAdmin);
        $this->assertTrue(Gate::allows('view_payroll'));
        $this->assertTrue(Gate::allows('view_config'));
        $this->assertTrue(Gate::allows('manage_user'));
    }

    public function test_check_role_middleware_blocks_low_ranking(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        $this->actingAs($employee)
            ->get(route('admin.master.holidays.index'))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('admin.home'))
            ->assertOk();
    }

    public function test_check_role_middleware_allows_hr_staff_for_master(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->get(route('admin.master.holidays.index'))
            ->assertOk();

        $admin = $this->employee('agus.setiawan', 'SUPER_ADMIN');
        $this->actingAs($admin)
            ->get(route('admin.master.holidays.create'))
            ->assertOk();
    }

    public function test_holiday_crud_follows_base_controller_pattern(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $holiday = $this->actingAs($staff)->post(route('admin.master.holidays.store'), [
            'holiday_date' => '2026-08-17',
            'name' => 'Hari Kemerdekaan',
        ]);

        $holiday->assertRedirect(route('admin.master.holidays.index'));

        $created = Holiday::where('name', 'Hari Kemerdekaan')->first();

        $this->assertNotNull($created);
        $this->assertSame('2026-08-17', $created->holiday_date?->format('Y-m-d'));

        $this->actingAs($staff)
            ->get(route('admin.master.holidays.edit', $created))
            ->assertOk();

        $this->actingAs($staff)
            ->put(route('admin.master.holidays.update', $created), [
                'holiday_date' => '2026-08-17',
                'name' => 'Hari Kemerdekaan RI',
            ])
            ->assertRedirect(route('admin.master.holidays.index'));

        $this->actingAs($staff)
            ->delete(route('admin.master.holidays.destroy', $created))
            ->assertRedirect(route('admin.master.holidays.index'));

        $this->assertSoftDeleted('holidays', ['id' => $created->getKey()]);
    }

    public function test_low_ranking_cannot_create_master_data(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        $this->actingAs($employee)
            ->post(route('admin.master.holidays.store'), [
                'holiday_date' => '2026-12-25',
                'name' => 'Natal',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('holidays', ['name' => 'Natal']);
    }

    public function test_company_context_is_sticky_and_validated(): void
    {
        $company = Company::create([
            'code' => 'CMP001',
            'name' => 'PT Maju Bersama',
            'birth_day' => '2010-01-01',
            'email' => 'cs@majubersama.test',
            'tax_number' => '1234567890',
        ]);

        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->withSession([])
            ->get(route('admin.master.holidays.index', ['company_id' => $company->getKey()]))
            ->assertOk()
            ->assertSessionHas('hris.company_id', $company->getKey());

        $this->actingAs($staff)
            ->withSession(['hris.company_id' => 'stale'])
            ->get(route('admin.master.holidays.index', ['company_id' => 'does-not-exist']))
            ->assertOk()
            ->assertSessionHas('hris.company_id', 'stale');
    }

    public function test_employee_context_is_sticky(): void
    {
        $target = $this->employee('dewi.lestari', 'HRSUPERVISOR');
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->withSession([])
            ->get(route('admin.master.holidays.index', ['employee_id' => $target->getKey()]))
            ->assertOk()
            ->assertSessionHas('hris.employee_id', $target->getKey());
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
}