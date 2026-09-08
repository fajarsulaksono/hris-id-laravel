<?php

namespace Tests\Feature;

use App\Domain\Salary\Processor\SalaryProcessor;
use App\Enums\RiskRatio;
use App\Enums\TaxGroup;
use App\Models\Company\Company;
use App\Models\Employee\Employee;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\SalaryBenefit;
use App\Models\Payroll\SalaryComponent;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SalaryComponentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PayrollUiTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(SalaryComponentSeeder::class);

        $this->company = Company::create([
            'code' => 'UI1',
            'name' => 'PT UI TEST',
            'birth_day' => '2020-01-01',
            'email' => 'ui@example.test',
            'tax_number' => '01.001.002.3-004.001',
        ]);

        $this->employee = Employee::create([
            'company_id' => $this->company->getKey(),
            'code' => 'EMPUI',
            'full_name' => 'Uji Interface',
            'username' => 'uji.interface',
            'email' => 'uji.interface@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2020-02-02',
            'date_of_birth' => '1991-03-03',
            'identity_number' => '3174010303910002',
            'tax_group' => TaxGroup::TK2,
            'risk_ratio' => RiskRatio::RISK_VERY_LOW,
            'have_overtime_benefit' => true,
        ]);

        $this->supervisor = Employee::create([
            'company_id' => $this->company->getKey(),
            'code' => 'SUP001',
            'full_name' => 'Supervisor HR',
            'username' => 'sup.hr',
            'email' => 'sup.hr@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2019-01-01',
            'date_of_birth' => '1988-05-05',
            'identity_number' => '3174010505880001',
        ]);

        $this->supervisor->assignRole('HRSUPERVISOR');
    }

    public function test_guest_cannot_access_payroll_pages(): void
    {
        $this->get(route('admin.payroll.payrolls.process'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.payroll.payrolls.tax'))
            ->assertRedirect(route('login'));
    }

    public function test_process_page_renders(): void
    {
        $this->actingAs($this->supervisor)
            ->get(route('admin.payroll.payrolls.process'))
            ->assertOk();
    }

    public function test_tax_page_renders(): void
    {
        $this->actingAs($this->supervisor)
            ->get(route('admin.payroll.payrolls.tax'))
            ->assertOk();
    }

    public function test_process_payroll_endpoint_runs_for_current_month(): void
    {
        $gp = SalaryComponent::where('code', 'GP')->first();
        SalaryBenefit::create([
            'employee_id' => $this->employee->getKey(),
            'component_id' => $gp->getKey(),
            'benefit_value' => 5_000_000,
        ]);

        $year = (int) now()->format('Y');
        $month = (int) now()->format('n');

        $this->actingAs($this->supervisor)
            ->post(route('admin.payroll.payrolls.process'), [
                'year' => $year,
                'month' => $month,
                'company_id' => $this->company->getKey(),
            ])
            ->assertRedirect(route('admin.payroll.payrolls.process'))
            ->assertSessionHas('success');

        $period = PayrollPeriod::where('company_id', $this->company->getKey())
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $this->assertNotNull($period);

        $this->assertSame(1, Payroll::where('employee_id', $this->employee->getKey())
            ->where('period_id', $period->getKey())
            ->count());
    }

    public function test_detail_page_renders_after_processing(): void
    {
        $gp = SalaryComponent::where('code', 'GP')->first();
        SalaryBenefit::create([
            'employee_id' => $this->employee->getKey(),
            'component_id' => $gp->getKey(),
            'benefit_value' => 5_000_000,
        ]);

        $period = PayrollPeriod::create([
            'company_id' => $this->company->getKey(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'closed' => false,
        ]);

        app(SalaryProcessor::class)->process($this->employee, now());

        $payroll = Payroll::where('employee_id', $this->employee->getKey())
            ->where('period_id', $period->getKey())
            ->first();

        $this->assertNotNull($payroll);

        $this->actingAs($this->supervisor)
            ->get(route('admin.payroll.payrolls.detail', $payroll->getKey()))
            ->assertOk()
            ->assertSee('Detail Payroll');
    }

    public function test_recap_page_renders_for_processed_period(): void
    {
        $gp = SalaryComponent::where('code', 'GP')->first();
        SalaryBenefit::create([
            'employee_id' => $this->employee->getKey(),
            'component_id' => $gp->getKey(),
            'benefit_value' => 5_000_000,
        ]);

        PayrollPeriod::create([
            'company_id' => $this->company->getKey(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'closed' => false,
        ]);

        app(SalaryProcessor::class)->process($this->employee, now());

        $year = (int) now()->format('Y');
        $month = (int) now()->format('n');

        $this->actingAs($this->supervisor)
            ->get(route('admin.payroll.payrolls.recap', [
                'year' => $year,
                'month' => $month,
                'company_id' => $this->company->getKey(),
            ]))
            ->assertOk()
            ->assertSee('Take Home Pay');
    }

    public function test_recap_exports_xlsx_and_pdf(): void
    {
        $this->actingAs($this->supervisor)
            ->get(route('admin.payroll.payrolls.recap.export', [
                'year' => (int) now()->format('Y'),
                'month' => (int) now()->format('n'),
                'format' => 'xlsx',
            ]))
            ->assertOk();

        $this->actingAs($this->supervisor)
            ->get(route('admin.payroll.payrolls.recap.export', [
                'year' => (int) now()->format('Y'),
                'month' => (int) now()->format('n'),
                'format' => 'pdf',
            ]))
            ->assertOk();
    }

    public function test_slip_pdf_exports(): void
    {
        $gp = SalaryComponent::where('code', 'GP')->first();
        SalaryBenefit::create([
            'employee_id' => $this->employee->getKey(),
            'component_id' => $gp->getKey(),
            'benefit_value' => 5_000_000,
        ]);

        PayrollPeriod::create([
            'company_id' => $this->company->getKey(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'closed' => false,
        ]);

        app(SalaryProcessor::class)->process($this->employee, now());

        $payroll = Payroll::where('employee_id', $this->employee->getKey())
            ->where('period_id', PayrollPeriod::query()
                ->where('company_id', $this->company->getKey())
                ->where('year', (int) now()->format('Y'))
                ->where('month', (int) now()->format('n'))
                ->value('id'))
            ->first();

        $this->assertNotNull($payroll);

        $this->actingAs($this->supervisor)
            ->get(route('admin.payroll.payrolls.pdf', $payroll->getKey()))
            ->assertOk();
    }
}
