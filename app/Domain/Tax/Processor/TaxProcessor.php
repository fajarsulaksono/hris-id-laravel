<?php

namespace App\Domain\Tax\Processor;

use App\Domain\Tax\InvalidCalculatorException;
use App\Domain\Tax\TaxCalculatorInterface;
use App\Models\Payroll\Payroll;

/**
 * Port dari SemartHris\Component\Tax\Processor\TaxProcessor:
 * menghitung PPh21 bulanan dari take home pay tahunan dikurangi PTKP,
 * memakai chain TaxCalculator (5%, 15%, 25%, 30%).
 */
class TaxProcessor implements TaxProcessorInterface
{
    private float $taxable = 0.0;

    private float $untaxable = 0.0;

    /** @var TaxCalculatorInterface[] */
    private array $calculators = [];

    public function __construct(array $calculators = [])
    {
        foreach ($calculators as $calculator) {
            $this->addCalculator($calculator);
        }
    }

    public function process(Payroll $payroll): float
    {
        $takeHomePay = (float) $payroll->take_home_pay;
        $this->untaxable = (float) ($payroll->employee?->tax_group?->ptkp() ?? 0);
        $this->taxable = (12 * $takeHomePay) - $this->untaxable;

        foreach ($this->calculators as $calculator) {
            if ($calculator->isSupportPkp($this->taxable)) {
                return round($calculator->calculate($this->taxable) / 12, 0, PHP_ROUND_HALF_DOWN);
            }
        }

        throw new InvalidCalculatorException();
    }

    public function getTaxableValue(): float
    {
        return $this->taxable;
    }

    public function setTaxableValue(?float $taxable): void
    {
        $this->taxable = $taxable;
    }

    public function getUntaxableValue(): float
    {
        return $this->untaxable;
    }

    public function setUntaxableValue(?float $untaxable): void
    {
        $this->untaxable = $untaxable;
    }

    private function addCalculator(TaxCalculatorInterface $calculator): void
    {
        $this->calculators[] = $calculator;
    }
}