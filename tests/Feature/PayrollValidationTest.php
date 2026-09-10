<?php

namespace Tests\Feature;

use App\Enums\RiskRatio;
use App\Enums\TaxGroup;
use App\Models\Company\Company;
use App\Models\Employee\Employee;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\SalaryBenefit;
use App\Models\Payroll\SalaryBenefitHistory;
use App\Models\Payroll\SalaryComponent;
use App\Models\Tax\TaxGroupHistory;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SalaryComponentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PayrollValidationTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Employee $employee;

    private Employee $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(SalaryComponentSeeder::class);

        $this->company = Company::create([
            'code' => 'PV1',
            'name' => 'PT PAYROLL VALIDATION',
            'birth_day' => '2020-01-01',
            'email' => 'pv@example.test',
            'tax_number' => '01.001.002.3-004.010',
        ]);

        $this->employee = Employee::create([
            'company_id' => $this->company->getKey(),
            'code' => 'EMPV1',
            'full_name' => 'Uji Validasi',
            'username' => 'uji.validasi',
            'email' => 'uji.validasi@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2020-02-02',
            'date_of_birth' => '1991-03-03',
            'identity_number' => '3174010303910010',
            'tax_group' => TaxGroup::TK2,
            'risk_ratio' => RiskRatio::RISK_VERY_LOW,
        ]);

        $this->supervisor = Employee::create([
            'company_id' => $this->company->getKey(),
            'code' => 'SUPPV',
            'full_name' => 'Supervisor PV',
            'username' => 'sup.pv',
            'email' => 'sup.pv@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2019-01-01',
            'date_of_birth' => '1988-05-05',
            'identity_number' => '3174010505880010',
        ]);

        $this->supervisor->assignRole('HRSUPERVISOR');
    }

    public function test_salary_benefit_blocked_when_employee_has_payroll(): void
    {
        $component = SalaryComponent::where('code', 'GP')->first();

        PayrollPeriod::create([
            'company_id' => $this->company->getKey(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'closed' => true,
        ]);

        Payroll::create([
            'employee_id' => $this->employee->getKey(),
            'period_id' => PayrollPeriod::where('company_id', $this->company->getKey())
                ->where('year', (int) now()->format('Y'))
                ->where('month', (int) now()->format('n'))
                ->first()->getKey(),
            'take_home_pay' => 5_000_000,
        ]);

        $this->actingAs($this->supervisor)
            ->post(route('admin.payroll.salary-benefits.store'), [
                'employee_id' => $this->employee->getKey(),
                'component_id' => $component->getKey(),
                'benefit_value' => 1_000_000,
            ])
            ->assertSessionHasErrors('employee_id');
    }

    public function test_salary_benefit_allowed_when_employee_has_no_payroll(): void
    {
        $component = SalaryComponent::where('code', 'GP')->first();

        $this->actingAs($this->supervisor)
            ->post(route('admin.payroll.salary-benefits.store'), [
                'employee_id' => $this->employee->getKey(),
                'component_id' => $component->getKey(),
                'benefit_value' => 5_000_000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('salary_benefits', [
            'employee_id' => $this->employee->getKey(),
            'component_id' => $component->getKey(),
        ]);
    }

    public function test_tax_group_history_blocked_when_no_change(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('admin.payroll.tax-group-histories.store'), [
                'employee_id' => $this->employee->getKey(),
                'new_tax_group' => TaxGroup::TK2->value,
                'new_risk_ratio' => RiskRatio::RISK_VERY_LOW->value,
            ])
            ->assertSessionHasErrors('new_tax_group');
    }

    public function test_tax_group_history_allowed_when_tax_group_changes(): void
    {
        $this->actingAs($this->supervisor)
            ->post(route('admin.payroll.tax-group-histories.store'), [
                'employee_id' => $this->employee->getKey(),
                'new_tax_group' => TaxGroup::K2->value,
                'new_risk_ratio' => RiskRatio::RISK_VERY_LOW->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tax_group_history', [
            'employee_id' => $this->employee->getKey(),
        ]);

        $this->employee->refresh();
        $this->assertSame(TaxGroup::K2, $this->employee->tax_group);
    }

    public function test_tax_group_history_update_applies_to_employee(): void
    {
        $history = TaxGroupHistory::create([
            'employee_id' => $this->employee->getKey(),
            'new_tax_group' => TaxGroup::K1->value,
            'new_risk_ratio' => RiskRatio::RISK_NORMAL->value,
        ]);

        $this->employee->refresh();
        $this->assertSame(TaxGroup::K1, $this->employee->tax_group);
        $this->assertSame(RiskRatio::RISK_NORMAL, $this->employee->risk_ratio);

        $history->update([
            'new_tax_group' => TaxGroup::K3->value,
            'new_risk_ratio' => RiskRatio::RISK_HIGH->value,
        ]);

        $this->employee->refresh();
        $this->assertSame(TaxGroup::K3, $this->employee->tax_group);
        $this->assertSame(RiskRatio::RISK_HIGH, $this->employee->risk_ratio);
    }

    public function test_change_benefit_guard_on_empty_value(): void
    {
        $component = SalaryComponent::where('code', 'GP')->first();

        SalaryBenefit::create([
            'employee_id' => $this->employee->getKey(),
            'component_id' => $component->getKey(),
            'benefit_value' => 5_000_000,
        ]);

        $history = SalaryBenefitHistory::create([
            'employee_id' => $this->employee->getKey(),
            'component_id' => $component->getKey(),
            'new_benefit_value' => 6_000_000,
            'description' => 'Kenaikan gaji',
        ]);

        $this->employee->refresh();
        $benefit = SalaryBenefit::where('employee_id', $this->employee->getKey())
            ->where('component_id', $component->getKey())
            ->first();
        $this->assertSame(6_000_000, (int) $benefit->benefit_value);

        $history->update(['new_benefit_value' => null]);

        $this->employee->refresh();
        $benefit->refresh();
        $this->assertSame(6_000_000, (int) $benefit->benefit_value);
    }
}
