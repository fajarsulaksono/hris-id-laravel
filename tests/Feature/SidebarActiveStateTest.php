<?php

namespace Tests\Feature;

use App\Models\Employee\Employee;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SidebarActiveStateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_dashboard_active_but_no_parent_menu_open_on_dashboard(): void
    {
        $html = $this->actingAs($this->employee('sari.wulandari', 'HRSTAFF'))
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('nav-link active', $html);
        $this->assertStringNotContainsString('nav-item menu-open', $html);
    }

    public function test_master_parent_open_and_submenu_active_on_master_page(): void
    {
        $html = $this->actingAs($this->employee('sari.wulandari', 'HRSTAFF'))
            ->get(route('admin.master.regions.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('nav-item menu-open', $html);
        $this->assertStringContainsString('nav-link active', $html);
    }

    public function test_attendance_parent_open_on_attendance_process_page(): void
    {
        $html = $this->actingAs($this->employee('sari.wulandari', 'HRSTAFF'))
            ->get(route('admin.attendance.attendances.process'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('nav-item menu-open', $html);
        $this->assertStringContainsString('nav-link active', $html);
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
