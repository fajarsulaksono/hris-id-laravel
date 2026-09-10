<?php

namespace App\Enums;

/**
 * JKK (Jaminan Kecelakaan Kerja) risk ratio sesuai PP 44/2021.
 *
 * Perbaikan disengaja: Symfony asli (RiskRatioConverter) selalu
 * menghasilkan 0.24% karena bug `in_array($code, $map)` pada key
 * float. Port ini menggunakan nilai nyata per-level.
 */
enum RiskRatio: string
{
    case RISK_VERY_HIGH = 'vhr';
    case RISK_HIGH = 'hr';
    case RISK_NORMAL = 'nr';
    case RISK_LOW = 'lr';
    case RISK_VERY_LOW = 'vlr';

    public function label(): string
    {
        return match ($this) {
            self::RISK_VERY_HIGH => 'RESIKO SANGAT TINGGI',
            self::RISK_HIGH => 'RESIKO TINGGI',
            self::RISK_NORMAL => 'RESIKO NORMAL',
            self::RISK_LOW => 'RESIKO RENDAH',
            self::RISK_VERY_LOW => 'RESIKO SANGAT RENDAH',
        };
    }

    public function value(): float
    {
        return match ($this) {
            self::RISK_VERY_HIGH => 0.0174,
            self::RISK_HIGH => 0.0127,
            self::RISK_NORMAL => 0.0089,
            self::RISK_LOW => 0.0054,
            self::RISK_VERY_LOW => 0.0024,
        };
    }
}
