<?php

namespace App\Enums;

/**
 * @author Muhamad Surya Iksanudin <surya.iksanudin@gmail.com>
 */
enum ContractType: string
{
    case PERMANENT = 'p';
    case TEMPORARY = 't';
    case OUTSOURCE = 'o';
    case INTERNSHIP = 'i';

    public function label(): string
    {
        return match ($this) {
            self::PERMANENT => 'KARYAWAN TETAP',
            self::TEMPORARY => 'KARYAWAN KONTRAK',
            self::OUTSOURCE => 'KARYAWAN OUTSOURCE',
            self::INTERNSHIP => 'KARYAWAN MAGANG',
        };
    }
}