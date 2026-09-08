<?php

namespace Tests\Feature;

use App\Models\Employee\Employee;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeCrudTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_employee_create_autogenerates_code_username_and_password(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->post(route('admin.employee.employees.store'), [
                'full_name' => 'andi prasetyo',
                'gender' => 'm',
                'join_date' => '2026-01-15',
                'date_of_birth' => '1995-04-20',
                'identity_number' => '1234567890123456',
                'email' => 'andi.prasetyo@example.test',
            ])
            ->assertRedirect(route('admin.employee.employees.index'));

        $employee = Employee::where('email', 'andi.prasetyo@example.test')->first();

        $this->assertNotNull($employee);
        $this->assertSame('EMP001', $employee->code);
        $this->assertSame('andi.prasetyo', $employee->username);
        $this->assertNotSame('1234567890', $employee->password);
        $this->assertTrue(Hash::check('1234567890', $employee->password));
        $this->assertTrue($employee->hasRole('EMPLOYEE'));
    }

    public function test_employee_username_is_unique_when_generated(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)->post(route('admin.employee.employees.store'), [
            'full_name' => 'agus setiawan',
            'gender' => 'm',
            'join_date' => '2026-01-15',
            'date_of_birth' => '1990-01-01',
            'identity_number' => '1111111111111111',
            'email' => 'agus.setiawan@example.test',
        ]);

        $this->actingAs($staff)->post(route('admin.employee.employees.store'), [
            'full_name' => 'agus setiawan',
            'gender' => 'm',
            'join_date' => '2026-02-15',
            'date_of_birth' => '1991-02-02',
            'identity_number' => '2222222222222222',
            'email' => 'agus.setiawan2@example.test',
        ])->assertRedirect(route('admin.employee.employees.index'));

        $this->assertNotNull(Employee::where('username', 'agus.setiawan2')->first());
        $this->assertSame(2, Employee::where('full_name', 'AGUS SETIAWAN')->count());
    }

    public function test_employee_profile_photo_uploaded_via_medialibrary(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->post(route('admin.employee.employees.store'), [
                'full_name' => 'budi santoso',
                'gender' => 'm',
                'join_date' => '2026-01-15',
                'date_of_birth' => '1992-03-03',
                'identity_number' => '3333333333333333',
                'email' => 'budi.santoso@example.test',
                'profile_image' => UploadedFile::fake()->image('foto.png', 100, 100),
            ])
            ->assertRedirect(route('admin.employee.employees.index'));

        $employee = Employee::where('email', 'budi.santoso@example.test')->first();

        $this->assertNotNull($employee);
        $this->assertCount(1, $employee->getMedia('profile'));
        $this->assertSame('foto.png', $employee->getFirstMedia('profile')->file_name);
        $this->assertNotSame('', $employee->getFirstMediaUrl('profile'));
    }

    public function test_employee_show_page_renders_photo(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        $employee->addMedia(UploadedFile::fake()->image('foto.png', 100, 100))
            ->toMediaCollection('profile');

        $url = $employee->getFirstMediaUrl('profile');

        $this->assertNotSame('', $url);

        $this->actingAs($staff)
            ->get(route('admin.employee.employees.show', $employee))
            ->assertOk()
            ->assertSee($url, false);
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
