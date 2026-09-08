<?php

namespace Tests\Feature;

use App\Models\Company\Company;
use App\Models\Company\Department;
use App\Models\Company\JobLevel;
use App\Models\Company\JobTitle;
use App\Models\Employee\CareerHistory;
use App\Models\Employee\Employee;
use App\Models\Employee\Mutation;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmployeeProfileTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_profile_page_shows_employee_personal_data(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        $this->actingAs($staff)
            ->get(route('admin.employee.employees.profile', $employee))
            ->assertOk()
            ->assertSee('Profil Karyawan')
            ->assertSee($employee->full_name)
            ->assertSee($employee->code)
            ->assertSee(route('admin.employee.employees.promotion', $employee), false);
    }

    public function test_profile_page_lists_career_history(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        CareerHistory::create([
            'employee_id' => $employee->id,
            'job_title_id' => null,
            'description' => 'MUTASI',
        ]);

        $this->actingAs($staff)
            ->get(route('admin.employee.employees.profile', $employee))
            ->assertOk()
            ->assertSee('Riwayat Karir')
            ->assertSee('MUTASI');
    }

    public function test_promotion_form_applies_new_job_and_records_history(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        $company = Company::create(['code' => 'PTX', 'name' => 'PT Test', 'birth_day' => '2000-01-01', 'email' => 'pt@test.id', 'tax_number' => '00.000.000.0-000.000']);
        $department = Department::create(['code' => 'MKT', 'name' => 'Marketing']);
        $jobLevel = JobLevel::create(['code' => 'L2', 'name' => 'Senior Staff']);
        $jobTitle = JobTitle::create(['code' => 'SPV', 'name' => 'Supervisor', 'job_level_id' => $jobLevel->id]);

        $this->actingAs($staff)
            ->post(route('admin.employee.employees.promotion.store', $employee), [
                'type' => 'p',
                'new_company_id' => $company->id,
                'new_department_id' => $department->id,
                'new_job_level_id' => $jobLevel->id,
                'new_job_title_id' => $jobTitle->id,
            ])
            ->assertRedirect(route('admin.employee.employees.profile', $employee));

        $mutation = Mutation::where('employee_id', $employee->id)->first();

        $this->assertNotNull($mutation);
        $this->assertSame('p', $mutation->type->value);
        $this->assertSame($employee->job_title_id, $mutation->old_job_title_id);
        $this->assertSame($jobTitle->id, $mutation->new_job_title_id);

        $employee->refresh();

        $this->assertSame($company->id, $employee->company_id);
        $this->assertSame($department->id, $employee->department_id);
        $this->assertSame($jobLevel->id, $employee->job_level_id);
        $this->assertSame($jobTitle->id, $employee->job_title_id);

        $history = CareerHistory::where('employee_id', $employee->id)->first();

        $this->assertNotNull($history);
        $this->assertSame('PROMOSI', $history->description);
        $this->assertSame($jobTitle->id, $history->job_title_id);
    }

    public function test_promotion_form_rejects_invalid_type(): void
    {
        $staff = $this->employee('sari.wulandari', 'HRSTAFF');
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        $this->actingAs($staff)
            ->post(route('admin.employee.employees.promotion.store', $employee), [
                'type' => 'x',
            ])
            ->assertSessionHasErrors('type');

        $this->assertNull(Mutation::where('employee_id', $employee->id)->first());
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
