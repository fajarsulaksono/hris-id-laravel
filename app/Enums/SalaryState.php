<?php

namespace App\Enums;

enum SalaryState: string
{
    case MINUS = 'm';
    case PLUS = 'p';

    public function label(): string
    {
        return match ($this) {
            self::MINUS => 'POTONGAN',
            self::PLUS => 'PEMASUKAN',
        };
    }
}