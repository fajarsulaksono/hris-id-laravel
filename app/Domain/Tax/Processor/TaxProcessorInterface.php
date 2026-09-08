<?php

namespace App\Domain\Tax\Processor;

use App\Models\Payroll\Payroll;

/**
 * Port dari SemartHris\Component\Tax\Processor\TaxProcessorInterface.
 */
interface TaxProcessorInterface
{
    public function process(Payroll $payroll): float;

    public function getTaxableValue(): float;

    public function getUntaxableValue(): float;
}