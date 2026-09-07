<?php

namespace App\Enums;

enum MaritalStatus: string
{
    case SINGLE = 's';
    case MARRIED = 'm';
    case DIVORCED = 'd';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE => 'BELUM KAWIN',
            self::MARRIED => 'KAWIN',
            self::DIVORCED => 'CERAI',
        };
    }
}