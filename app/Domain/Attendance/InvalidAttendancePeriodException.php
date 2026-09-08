<?php

namespace App\Domain\Attendance;

class InvalidAttendancePeriodException extends \RuntimeException
{
    public function __construct(\DateTimeInterface $date, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Attendance with period %s month %s is not valid', $date->format('Y'), $date->format('m')),
            $code,
            $previous
        );
    }
}