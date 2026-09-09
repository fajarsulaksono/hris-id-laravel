<?php

namespace Tests\Feature;

use App\Models\Employee\Employee;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserMenuAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_hrstaff_can_manage_user_accounts(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $target = $this->employee('budi.santoso', 'EMPLOYEE');

        $this->actingAs($staff)
            ->get(route('admin.users.index'))
            ->assertOk();

        $this->actingAs($staff)
            ->get(route('admin.users.data', ['draw' => 1]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2);

        $this->actingAs($staff)
            ->put(route('admin.users.update', $target), [
                'username' => 'budi.baru',
                'email' => 'budi.baru@example.test',
                'role' => 'EMPLOYEE',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('budi.baru', $target->fresh()->username);
        $this->assertTrue($target->fresh()->hasRole('EMPLOYEE'));
    }

    public function test_lower_rank_cannot_promote_or_edit_higher_rank(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $super = $this->employee('agus.setiawan', 'SUPER_ADMIN');

        $this->actingAs($staff)
            ->get(route('admin.users.edit', $super))
            ->assertForbidden();

        $this->actingAs($staff)
            ->put(route('admin.users.update', $super), [
                'username' => $super->username,
                'email' => $super->email,
                'role' => 'EMPLOYEE',
            ])
            ->assertForbidden();
    }

    public function test_last_super_admin_cannot_be_demoted(): void
    {
        $super = $this->employee('agus.setiawan', 'SUPER_ADMIN');

        $this->actingAs($super)
            ->put(route('admin.users.update', $super), [
                'username' => $super->username,
                'email' => $super->email,
                'role' => 'HRSTAFF',
            ])
            ->assertSessionHasErrors('role');

        $this->assertTrue($super->fresh()->hasRole('SUPER_ADMIN'));
    }

    public function test_config_page_only_for_super_admin(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $super = $this->employee('agus.setiawan', 'SUPER_ADMIN');

        $this->actingAs($staff)
            ->get(route('admin.config.index'))
            ->assertForbidden();

        $this->actingAs($super)
            ->get(route('admin.config.index'))
            ->assertOk()
            ->assertSee('Akses Menu (Role Minimal)')
            ->assertSee('SUPER_ADMIN');
    }

    public function test_address_modules_are_under_address_menu(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->get(route('admin.address.employee-addresses.index'))
            ->assertOk()
            ->assertSee('Alamat Karyawan');

        $this->actingAs($staff)
            ->get(route('admin.address.company-addresses.index'))
            ->assertOk()
            ->assertSee('Alamat Perusahaan');
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
