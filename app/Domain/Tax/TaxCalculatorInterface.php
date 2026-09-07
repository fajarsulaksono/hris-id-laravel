<?php

namespace App\Domain\Tax;

interface TaxCalculatorInterface
{
    public function calculate(float $pkp): float;

    public function isSupportPkp(float $pkp): bool;

    public function maxPkp(): float;

    public function minPkp(): float;

    public function getPrevious(): ?self;

    public function setPrevious(?self $taxCalculator): void;

    public function taxPercentage(): float;
}