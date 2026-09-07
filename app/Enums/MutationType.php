<?php

namespace App\Enums;

enum MutationType: string
{
    case PROMOTION = 'p';
    case DEMOTION = 'd';
    case MUTATION = 'm';

    public function label(): string
    {
        return match ($this) {
            self::PROMOTION => 'PROMOSI',
            self::DEMOTION => 'DEMOSI',
            self::MUTATION => 'MUTASI',
        };
    }
}