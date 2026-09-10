<?php

namespace App\Domain\Tax\Service;

use App\Domain\Salary\Service\StoreAsCompanyCost;
use App\Domain\Tax\Processor\TaxProcessorInterface;
use App\Models\Employee\Employee;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollDetail;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\SalaryComponent;
use App\Models\Tax\Tax;

/**
 * Port dari SemartHris\Component\Tax\Service\TaxProcessor:
 * menghitung pajak payroll, mencatat detail PPH21 (+/-), dan menutup periode.
 */
class TaxProcessor
{
    public function __construct(
        private TaxProcessorInterface $taxProcessor,
        private StoreAsCompanyCost $storeAsCompanyCost,
        private string $taxPlusCode,
        private string $taxMinusCode,
    ) {}

    public function process(Employee $employee, PayrollPeriod $period): void
    {
        $payroll = Payroll::query()
            ->where('employee_id', $employee->getKey())
            ->where('period_id', $period->getKey())
            ->first();

        if (! $payroll) {
            throw new \InvalidArgumentException('Payroll not exist.');
        }

        $existingTax = Tax::where('period_id', $period->getKey())
            ->where('employee_id', $employee->getKey())
            ->first();

        $tax = $existingTax ?? new Tax([
            'period_id' => $period->getKey(),
            'employee_id' => $employee->getKey(),
            'tax_group' => $employee->tax_group,
        ]);
        $tax->tax_value = (string) $this->taxProcessor->process($payroll);
        $tax->taxable = (string) $this->taxProcessor->getTaxableValue();
        $tax->untaxable = (string) $this->taxProcessor->getUntaxableValue();

        $taxPlus = SalaryComponent::query()->where('code', $this->taxPlusCode)->first();
        if ($taxPlus) {
            $taxPlusBenefit = $this->createDetail($payroll, $taxPlus);
            $taxPlusBenefit->benefit_value = (string) $tax->tax_value;

            $this->storeAsCompanyCost->store($taxPlusBenefit);
            $taxPlusBenefit->save();
        }

        $taxMinus = SalaryComponent::query()->where('code', $this->taxMinusCode)->first();
        if (! $taxMinus) {
            throw new \RuntimeException('Tax minus benefit code is not valid.');
        }

        $taxMinusBenefit = $this->createDetail($payroll, $taxMinus);
        $taxMinusBenefit->benefit_value = (string) $tax->tax_value;

        $period->closed = true;

        $tax->save();
        $payroll->save();
        $this->storeAsCompanyCost->store($taxMinusBenefit);
        $taxMinusBenefit->save();
        $period->save();
    }

    private function createDetail(Payroll $payroll, SalaryComponent $component): PayrollDetail
    {
        return PayrollDetail::firstOrNew([
            'payroll_id' => $payroll->getKey(),
            'component_id' => $component->getKey(),
        ]);
    }
}
