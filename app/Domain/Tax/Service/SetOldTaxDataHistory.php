<?php

namespace App\Domain\Tax\Service;

use App\Models\Tax\TaxGroupHistory;

/**
 * Port dari SemartHris\Component\Tax\Service\SetOldTaxDataHistory:
 * merekam nilai lama tax group / risk ratio karyawan ke riwayat.
 */
class SetOldTaxDataHistory
{
    public static function setOldTaxData(TaxGroupHistory $history): void
    {
        $employee = $history->employee;

        $history->old_tax_group = $employee->tax_group;
        $history->old_risk_ratio = $employee->risk_ratio;
    }
}