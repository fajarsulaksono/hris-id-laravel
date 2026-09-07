<?php

namespace App\Enums;

enum IdentityType: string
{
    case DRIVER_LICENSE = 's';
    case PASSPORT = 'p';
    case ID_CARD = 'k';

    public function label(): string
    {
        return match ($this) {
            self::DRIVER_LICENSE => 'SIM',
            self::PASSPORT => 'PASPOR',
            self::ID_CARD => 'KTP',
        };
    }
}