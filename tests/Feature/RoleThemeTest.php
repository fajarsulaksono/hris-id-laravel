<?php

namespace Tests\Feature;

use App\Models\Employee\Employee;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_body_exposes_role_theme_for_employee(): void
    {
        $html = $this->actingAs($this->employee('budi.santoso', 'EMPLOYEE'))
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-role-theme="employee"', $html);
    }

    public function test_body_exposes_role_theme_for_hr_staff(): void
    {
        $html = $this->actingAs($this->employee('sari.wulandari', 'HRSTAFF'))
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-role-theme="hrstaff"', $html);
    }

    public function test_body_exposes_role_theme_for_super_admin(): void
    {
        $html = $this->actingAs($this->employee('agus.setiawan', 'SUPER_ADMIN'))
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-role-theme="super-admin"', $html);
    }

    public function test_highest_role_wins_when_user_has_multiple_roles(): void
    {
        $employee = $this->employee('multi.role', 'EMPLOYEE');
        $employee->assignRole('SUPER_ADMIN');

        $html = $this->actingAs($employee)
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-role-theme="super-admin"', $html);
    }

    private function employee(string $username, string $role): Employee
    {
        $employee = Employee::create([
            'code' => 'EMP-'.Str::upper(Str::random(6)),
            'full_name' => 'Test User',
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
