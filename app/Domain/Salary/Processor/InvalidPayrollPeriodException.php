<?php

namespace App\Domain\Salary\Processor;

use Throwable;

/**
 * Port dari SemartHris\Component\Salary\Processor\InvalidPayrollPeriodException.
 */
class InvalidPayrollPeriodException extends \RuntimeException
{
    public function __construct(\DateTimeInterface $date, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Payroll with period %s month %s is not valid or be closed', $date->format('Y'), $date->format('m')),
            $code,
            $previous
        );
    }
}