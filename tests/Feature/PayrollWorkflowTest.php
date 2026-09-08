<?php

namespace Tests\Feature;

use App\Domain\Salary\Processor\SalaryProcessor;
use App\Domain\Salary\Service\PayrollProcessor;
use App\Domain\Tax\Service\TaxProcessor as TaxProcessorService;
use App\Enums\RiskRatio;
use App\Enums\TaxGroup;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Company\Company;
use App\Models\Employee\Employee;
use App\Models\Payroll\CompanyCost;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollDetail;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\SalaryAllowance;
use App\Models\Payroll\SalaryBenefit;
use App\Models\Payroll\SalaryComponent;
use App\Models\Tax\Tax;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SalaryComponentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PayrollWorkflowTest extends TestCase
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
            'code' => 'CPA',
            'name' => 'PT PAYROLL TEST',
            'birth_day' => '2020-01-01',
            'email' => 'payroll@example.test',
            'tax_number' => '01.001.002.3-004.000',
        ]);

        $this->employee = Employee::create([
            'company_id' => $this->company->getKey(),
            'code' => 'EMP900',
            'full_name' => 'Uji Payroll',
            'username' => 'uji.payroll',
            'email' => 'uji.payroll@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2020-02-02',
            'date_of_birth' => '1991-03-03',
            'identity_number' => '3174010303910001',
            'tax_group' => TaxGroup::TK2,
            'risk_ratio' => RiskRatio::RISK_VERY_LOW,
            'have_overtime_benefit' => true,
        ]);

        $this->benefit('GP', 5_000_000);
        $this->benefit('TJ', 2_000_000);
    }

    private function benefit(string $code, int $value): void
    {
        $component = SalaryComponent::where('code', $code)->first();

        SalaryBenefit::create([
            'employee_id' => $this->employee->getKey(),
            'component_id' => $component->getKey(),
            'benefit_value' => $value,
        ]);
    }

    private function allowance(string $code, int $value): void
    {
        $component = SalaryComponent::where('code', $code)->first();

        SalaryAllowance::create([
            'employee_id' => $this->employee->getKey(),
            'component_id' => $component->getKey(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'benefit_value' => $value,
        ]);
    }

    public function test_payroll_salary_chain_computes_take_home_pay(): void
    {
        $this->allowance('UM', 300_000);
        $this->allowance('PL', 250_000);

        AttendanceSummary::create([
            'employee_id' => $this->employee->getKey(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'total_workday' => 20,
            'total_in' => 20,
            'total_loyality' => 0,
            'total_absent' => 0,
            'total_overtime' => 5,
        ]);

        $this->createPeriod();

        $this->salaryProcessor()->process($this->employee, now());

        $period = $this->period();

        $this->assertNotNull($period, 'payroll period should be created');

        $payroll = Payroll::where('employee_id', $this->employee->getKey())
            ->where('period_id', $period->getKey())
            ->first();

        $this->assertNotNull($payroll, 'payroll row should exist');

        // fixed (7.000.000) + lembur (1/173 * 7.000.000 * 5 = 202.312) + UM (300.000) - PL (250.000)
        $this->assertSame(7_252_312, (int) $payroll->take_home_pay);

        $gp = $payroll->details()
            ->whereHas('component', fn ($q) => $q->where('code', 'GP'))
            ->get()
            ->sum(fn ($detail) => (int) $detail->benefit_value);

        $this->assertSame(5_000_000, $gp);
    }

    public function test_payroll_processing_is_idempotent(): void
    {
        $this->createPeriod();

        $this->salaryProcessor()->process($this->employee, now());
        $this->salaryProcessor()->process($this->employee, now());

        $payroll = Payroll::where('employee_id', $this->employee->getKey())
            ->where('period_id', $this->period()->getKey())
            ->first();

        $this->assertSame(7_000_000, (int) $payroll->take_home_pay);

        // 2 tunjangan tetap + 4 detail BPJS pegawai (JHTP/JHTM/JPP/JPM)
        $this->assertSame(6, PayrollDetail::where('payroll_id', $payroll->getKey())->count());
    }

    public function test_tax_processor_computes_monthly_pph21_and_closes_period(): void
    {
        $this->allowance('UM', 300_000);
        $this->allowance('PL', 250_000);

        AttendanceSummary::create([
            'employee_id' => $this->employee->getKey(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'total_workday' => 20,
            'total_in' => 20,
            'total_loyality' => 0,
            'total_absent' => 0,
            'total_overtime' => 5,
        ]);

        $this->createPeriod();

        $this->salaryProcessor()->process($this->employee, now());

        $period = $this->period();

        app(TaxProcessorService::class)->process($this->employee, $period);

        $tax = Tax::where('employee_id', $this->employee->getKey())
            ->where('period_id', $period->getKey())
            ->first();

        $this->assertNotNull($tax, 'tax row should exist');

        // take home 7.252.312, TK2 PTKP 63.000.000
        // PKP = (12 * 7.252.312) - 63.000.000 = 24.027.744 -> 5% = 1.201.387,2 -> /12 = 100.115,6 -> 100.116
        $this->assertSame(100_116, (int) $tax->tax_value);
        $this->assertSame(24_027_744, (int) $tax->taxable);
        $this->assertSame(63_000_000, (int) $tax->untaxable);

        $this->assertTrue($period->fresh()->closed, 'period should be closed after tax');

        $payrollId = $this->payrollId($period);

        $minus = PayrollDetail::where('payroll_id', $payrollId)
            ->whereHas('component', fn ($q) => $q->where('code', 'PPH21M'))
            ->first();

        $this->assertSame(100_116, (int) $minus->benefit_value);
    }

    public function test_bpjs_company_costs_are_recorded(): void
    {
        $this->createPeriod();

        $this->salaryProcessor()->process($this->employee, now());

        $payrollId = $this->payrollId($this->period());

        $cost = fn (string $code) => CompanyCost::where('payroll_id', $payrollId)
            ->whereHas('component', fn ($q) => $q->where('code', $code))
            ->value('benefit_value');

        // risk very low 0,24% * 7.000.000
        $this->assertSame(16_800, (int) $cost('JKK'));
        $this->assertSame(21_000, (int) $cost('JKM'));
        $this->assertSame(259_000, (int) $cost('JHTC'));
        $this->assertSame(140_000, (int) $cost('JPC'));
    }

    private function createPeriod(): PayrollPeriod
    {
        return PayrollPeriod::create([
            'company_id' => $this->company->getKey(),
            'year' => (int) now()->format('Y'),
            'month' => (int) now()->format('n'),
            'closed' => false,
        ]);
    }

    private function salaryProcessor(): \App\Domain\Salary\Processor\SalaryProcessor
    {
        return app(SalaryProcessor::class);
    }

    private function period(): ?PayrollPeriod
    {
        return PayrollPeriod::where('company_id', $this->company->getKey())
            ->where('year', (int) now()->format('Y'))
            ->where('month', (int) now()->format('n'))
            ->first();
    }

    private function payrollId(PayrollPeriod $period): string
    {
        return Payroll::where('employee_id', $this->employee->getKey())
            ->where('period_id', $period->getKey())
            ->value('id');
    }
}