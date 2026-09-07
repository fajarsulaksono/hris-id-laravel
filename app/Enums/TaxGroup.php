<?php

namespace App\Enums;

/**
 * Tax group sesuai aturan PTKP (Penghasilan Tidak Kena Pajak) Indonesia.
 *
 * @see https://www.online-pajak.com/id/ptkp-terbaru-pph-21
 */
enum TaxGroup: string
{
    case TK0 = 'tk0';
    case TK1 = 'tk1';
    case TK2 = 'tk2';
    case TK3 = 'tk3';
    case K0 = 'k0';
    case K1 = 'k1';
    case K2 = 'k2';
    case K3 = 'k3';
    case KI0 = 'ki0';
    case KI1 = 'ki1';
    case KI2 = 'ki2';
    case KI3 = 'ki3';

    public function ptkp(): int
    {
        return match ($this) {
            self::TK0 => 54000000,
            self::TK1 => 58500000,
            self::TK2 => 63000000,
            self::TK3 => 67500000,
            self::K0 => 58500000,
            self::K1 => 63000000,
            self::K2 => 67500000,
            self::K3 => 72000000,
            self::KI0 => 112500000,
            self::KI1 => 117000000,
            self::KI2 => 121500000,
            self::KI3 => 126000000,
        };
    }

    public function isTkGroup(): bool
    {
        return str_starts_with($this->value, 'tk');
    }

    public function isKGroup(): bool
    {
        return str_starts_with($this->value, 'k');
    }

    public function isKiGroup(): bool
    {
        return str_starts_with($this->value, 'ki');
    }
}