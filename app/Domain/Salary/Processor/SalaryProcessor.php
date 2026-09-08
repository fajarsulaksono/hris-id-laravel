<?php

namespace App\Domain\Salary\Processor;

use App\Domain\Salary\PayrollProcessorInterface;
use App\Domain\Salary\SalaryProcessorInterface;
use App\Domain\Salary\Service\StoreAsCompanyCost;
use App\Enums\SalaryState;
use App\Models\Employee\Employee;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollDetail;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\SalaryAllowance;
use App\Models\Payroll\SalaryBenefit;
use App\Models\Payroll\SalaryComponent;

/**
 * Port dari SemartHris\Component\Salary\Processor\SalaryProcessor:
 * menghitung gaji pokok (tunjangan tetap), tunjangan lembur, BPJS pegawai,
 * dan tunjangan/potongan lain, lalu menyimpan take home pay.
 */
class SalaryProcessor implements PayrollProcessorInterface
{
    /**
     * Kode komponen BPJS yang tidak termasuk tunjangan tetap karyawan
     * (dihitung terpisah oleh BpjsProcessor / dibebankan ke perusahaan).
     */
    public const BPJS_COMPONENT_CODES = [
        'JKK',
        'JKM',
        'JHTP',
        'JHTM',
        'JHTC',
        'JPP',
        'JPM',
        'JPC',
    ];

    /** @var SalaryProcessorInterface[] */
    private array $processors = [];

    public function __construct(
        private StoreAsCompanyCost $storeAsCompanyCost,
        array $processors = [],
    ) {
        foreach ($processors as $processor) {
            $this->addProcessor($processor);
        }
    }

    public function process(Employee $employee, \DateTimeInterface $date): void
    {
        $payrollPeriod = PayrollPeriod::query()
            ->where('company_id', $employee->company_id)
            ->where('year', (int) $date->format('Y'))
            ->where('month', (int) $date->format('n'))
            ->first();

        if (! $payrollPeriod || $payrollPeriod->closed) {
            throw new InvalidPayrollPeriodException($date);
        }

        $payroll = Payroll::firstOrNew([
            'employee_id' => $employee->getKey(),
            'period_id' => $payrollPeriod->getKey(),
        ]);

        $payroll->save();

        $fixedSalary = $this->processFixedBenefit($employee, $payroll);

        $takeHomePay = $fixedSalary;
        foreach ($this->processors as $processor) {
            $takeHomePay += $processor->process($payroll, $employee, $date, $fixedSalary);
        }

        $takeHomePay += $this->processAllowance($employee, $date, $payroll);

        $payroll->take_home_pay = (string) $takeHomePay;
        $payroll->save();
    }

    private function processFixedBenefit(Employee $employee, Payroll $payroll): float
    {
        $totalBenefit = 0.0;

        $benefits = SalaryBenefit::query()
            ->where('employee_id', $employee->getKey())
            ->whereHas('component', fn ($query) => $query
                ->where('fixed', true)
                ->whereNotIn('code', self::BPJS_COMPONENT_CODES))
            ->get();

        foreach ($benefits as $benefit) {
            $benefitValue = (float) $benefit->benefit_value;
            $totalBenefit += $benefitValue;

            $payrollDetail = $this->createPayrollDetail($payroll, $benefit->component);
            $payrollDetail->benefit_value = (string) $benefitValue;

            $this->storeDetail($payrollDetail);
        }

        return $totalBenefit;
    }

    private function processAllowance(Employee $employee, \DateTimeInterface $date, Payroll $payroll): float
    {
        $totalAllowance = 0.0;

        $allowances = SalaryAllowance::query()
            ->where('employee_id', $employee->getKey())
            ->where('year', (int) $date->format('Y'))
            ->where('month', (int) $date->format('n'))
            ->get();

        foreach ($allowances as $allowance) {
            $benefitValue = (float) $allowance->benefit_value;

            if ($allowance->component && $allowance->component->state === SalaryState::PLUS) {
                $totalAllowance += $benefitValue;
            } else {
                $totalAllowance -= $benefitValue;
            }

            $payrollDetail = $this->createPayrollDetail($payroll, $allowance->component);
            $payrollDetail->benefit_value = (string) $benefitValue;

            $this->storeDetail($payrollDetail);
        }

        return $totalAllowance;
    }

    private function createPayrollDetail(Payroll $payroll, ?SalaryComponent $component): PayrollDetail
    {
        return PayrollDetail::firstOrNew([
            'payroll_id' => $payroll->getKey(),
            'component_id' => $component?->getKey(),
        ]);
    }

    private function storeDetail(PayrollDetail $detail): void
    {
        $this->storeAsCompanyCost->store($detail);
        $detail->save();
    }

    private function addProcessor(SalaryProcessorInterface $processor): void
    {
        $this->processors[] = $processor;
    }
}