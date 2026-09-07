<?php

namespace App\Domain\Tax;

class FirstRateTaxCalculator extends AbstractTaxCalculator
{
    public function maxPkp(): float
    {
        return 50000000;
    }

    public function minPkp(): float
    {
        return 0;
    }

    public function taxPercentage(): float
    {
        return 0.05;
    }
}