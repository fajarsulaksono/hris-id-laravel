<?php

namespace Tests\Feature;

use App\Models\Employee\Employee;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class BreadcrumbTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_module_crud_page_renders_trail_home_menu_module(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $html = $this->actingAs($staff)
            ->get(route('admin.employee.employee-families.index'))
            ->assertOk()
            ->getContent();

        $breadcrumb = $this->breadcrumbHtml($html);

        $this->assertStringContainsString('Home', $breadcrumb);
        $this->assertStringContainsString('Karyawan', $breadcrumb);
        $this->assertStringContainsString('Data Keluarga', $breadcrumb);
        $this->assertSame(3, substr_count($breadcrumb, 'breadcrumb-item'));
    }

    public function test_self_service_page_renders_trail_home_data_saya_page(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        $html = $this->actingAs($employee)
            ->get(route('my.payrolls'))
            ->assertOk()
            ->getContent();

        $breadcrumb = $this->breadcrumbHtml($html);

        $this->assertStringContainsString('Data Saya', $breadcrumb);
        $this->assertStringContainsString('Slip Gaji Saya', $breadcrumb);
    }

    protected function breadcrumbHtml(string $html): string
    {
        $start = strpos($html, '<ol class="breadcrumb');

        $this->assertNotFalse($start, 'breadcrumb not found');

        $end = strpos($html, '</ol>', $start);

        return substr($html, $start, $end - $start);
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
