<?php

namespace App\Domain\Tax;

class ThirdRateTaxCalculator extends AbstractTaxCalculator
{
    public function maxPkp(): float
    {
        return 500000000;
    }

    public function minPkp(): float
    {
        return 250000000;
    }

    public function taxPercentage(): float
    {
        return 0.25;
    }
}