<?php

namespace App\Enums;

enum ReasonType: string
{
    case ABSENT = 'a';
    case LEAVE = 'l';

    public function label(): string
    {
        return match ($this) {
            self::ABSENT => 'ABSENSI',
            self::LEAVE => 'CUTI',
        };
    }
}