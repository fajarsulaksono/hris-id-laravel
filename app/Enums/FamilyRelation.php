<?php

namespace App\Enums;

enum FamilyRelation: string
{
    case PARENT = 'p';
    case COUPLE = 'c';
    case SON = 's';

    public function label(): string
    {
        return match ($this) {
            self::PARENT => 'ORANG TUA',
            self::COUPLE => 'SUAMI/ISTRI',
            self::SON => 'ANAK',
        };
    }
}