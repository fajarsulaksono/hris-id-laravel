<?php

namespace Tests\Feature;

use App\Models\Employee\Employee;
use App\Models\Employee\EmployeeEducation;
use App\Models\Employee\EmployeeFamily;
use App\Models\Employee\EmployeeSkill;
use App\Models\Master\EducationalInstitute;
use App\Models\Master\EducationTitle;
use App\Models\Master\Skill;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeePersonalDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_family_crud_list_and_store(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');

        $this->actingAs($staff)
            ->post(route('admin.employee.employee-families.store'), [
                'employee_id' => $staff->getKey(),
                'relation' => 'c',
                'name' => 'rini anggraeni',
                'gender' => 'f',
            ])
            ->assertRedirect(route('admin.employee.employee-families.index'));

        $family = EmployeeFamily::where('name', 'RINI ANGGRAENI')->first();

        $this->assertNotNull($family);
        $this->assertSame('SUAMI/ISTRI', $family->relation_text);

        $this->actingAs($staff)
            ->get(route('admin.employee.employee-families.data', ['draw' => 1]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.name', 'RINI ANGGRAENI');
    }

    public function test_education_module_renders_and_accessors(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $institute = EducationalInstitute::create(['name' => 'UNIVERSITAS INDONESIA']);
        $title = EducationTitle::create(['short_name' => 'S1', 'name' => 'SARJANA']);

        $education = EmployeeEducation::create([
            'employee_id' => $staff->getKey(),
            'education_institute_id' => $institute->getKey(),
            'education_title_id' => $title->getKey(),
            'year' => 2016,
        ]);

        $this->assertSame('SARJANA', $education->education_title_name);
        $this->assertSame('UNIVERSITAS INDONESIA', $education->education_institute_name);

        $this->actingAs($staff)
            ->get(route('admin.employee.employee-educations.index'))
            ->assertOk()
            ->assertSee('Pendidikan');
    }

    public function test_skill_module_stores_and_level_label(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $skill = Skill::create(['name' => 'MICROSOFT EXCEL']);

        $this->actingAs($staff)
            ->post(route('admin.employee.employee-skills.store'), [
                'employee_id' => $staff->getKey(),
                'skill_id' => $skill->getKey(),
                'level' => 'a',
            ])
            ->assertRedirect(route('admin.employee.employee-skills.index'));

        $row = EmployeeSkill::where('employee_id', $staff->getKey())->first();

        $this->assertNotNull($row);
        $this->assertSame('MAHIR', $row->level_text);
        $this->assertSame('MICROSOFT EXCEL', $row->skill_name);
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
