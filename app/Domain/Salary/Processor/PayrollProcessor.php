<?php

namespace App\Domain\Salary\Processor;

use App\Domain\Salary\PayrollProcessorInterface;
use App\Models\Employee\Employee;

/**
 * Port dari SemartHris\Component\Salary\Processor\PayrollProcessor:
 * orchestrator chain payroll (Attendance -> Salary).
 */
class PayrollProcessor implements PayrollProcessorInterface
{
    /** @var PayrollProcessorInterface[] */
    private array $processors = [];

    public function __construct(array $processors = [])
    {
        foreach ($processors as $processor) {
            $this->addProcessor($processor);
        }
    }

    public function process(Employee $employee, \DateTimeInterface $date): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($employee, $date);
        }
    }

    private function addProcessor(PayrollProcessorInterface $processor): void
    {
        $this->processors[] = $processor;
    }
}