<?php

namespace App\Observers;

use App\Domain\Salary\Service\ChangeBenefit;
use App\Models\Payroll\SalaryBenefitHistory;

class SalaryBenefitHistoryObserver
{
    public function __construct(protected ChangeBenefit $changeBenefit)
    {
    }

    /**
     * Port ChangeBenefitSubscriber (prePersist + preUpdate): saat riwayat
     * tunjangan dibuat/diubah, catat nilai lama dan terapkan nilai baru.
     */
    public function saving(SalaryBenefitHistory $benefitHistory): void
    {
        if (! $benefitHistory->employee_id || ! $benefitHistory->component_id) {
            return;
        }

        $this->changeBenefit->apply($benefitHistory);
    }
}