<?php

namespace App\Domain\Salary\Service;

use App\Models\Payroll\SalaryBenefit;
use App\Models\Payroll\SalaryBenefitHistory;

/**
 * Port dari SemartHris\Component\Salary\Service\ChangeBenefit:
 * record old + apply new saat tunjangan tetap diganti lewat riwayat.
 * Nilai terenkripsi ditangani transparan oleh SalaryCast.
 */
class ChangeBenefit
{
    public function apply(SalaryBenefitHistory $benefitHistory): void
    {
        $oldBenefit = SalaryBenefit::query()
            ->where('employee_id', $benefitHistory->employee_id)
            ->where('component_id', $benefitHistory->component_id)
            ->first();

        if (! $oldBenefit) {
            throw new \InvalidArgumentException(sprintf(
                "Employee %s doesn't has %s benefit",
                $benefitHistory->employee_id,
                $benefitHistory->component_id
            ));
        }

        $benefitHistory->old_benefit_value = (string) $oldBenefit->benefit_value;
        $oldBenefit->benefit_value = (string) $benefitHistory->new_benefit_value;

        $oldBenefit->save();
    }
}