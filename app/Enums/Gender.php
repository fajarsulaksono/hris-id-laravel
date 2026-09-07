<?php

namespace App\Enums;

enum Gender: string
{
    case MALE = 'm';
    case FEMALE = 'f';

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'PRIA',
            self::FEMALE => 'WANITA',
        };
    }
}