<?php

namespace App\Enums;

enum SkillLevel: string
{
    case BEGINNER = 'b';
    case INTERMEDIATE = 'i';
    case ADVANCED = 'a';
    case EXPERT = 'e';

    public function label(): string
    {
        return match ($this) {
            self::BEGINNER => 'PEMULA',
            self::INTERMEDIATE => 'MENENGAH',
            self::ADVANCED => 'MAHIR',
            self::EXPERT => 'AHLI',
        };
    }
}
