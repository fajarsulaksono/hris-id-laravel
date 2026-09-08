<?php

namespace App\Domain\Tax\Service;

use App\Models\Tax\TaxGroupHistory;

/**
 * Port dari SemartHris\Component\Tax\Service\SetNewTaxDataHistory:
 * menerapkan nilai baru tax group / risk ratio ke karyawan.
 */
class SetNewTaxDataHistory
{
    public static function setNewTaxData(TaxGroupHistory $history): void
    {
        $employee = $history->employee;
        $taxGroup = $history->new_tax_group ?? $employee->tax_group;
        $riskRatio = $history->new_risk_ratio ?? $employee->risk_ratio;

        $employee->tax_group = $taxGroup;
        $employee->risk_ratio = $riskRatio;

        $employee->save();
    }
}