<?php

namespace App\Domain\Tax\Service;

use App\Models\Tax\TaxGroupHistory;

/**
 * Port dari SemartHris\Component\Tax\Service\ValidateTaxHistory:
 * riwayat dianggap valid bila setidaknya ada satu perubahan (tax group atau risk ratio).
 */
class ValidateTaxHistory
{
    public static function validate(TaxGroupHistory $history): bool
    {
        $count = 0;
        $employee = $history->employee;

        if ($employee->risk_ratio === $history->new_risk_ratio || ! $history->new_risk_ratio) {
            ++$count;
        }

        if ($employee->tax_group === $history->new_tax_group || ! $history->new_tax_group) {
            ++$count;
        }

        return 2 !== $count;
    }
}