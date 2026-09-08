<?php

namespace App\Domain\Salary\Service;

use App\Models\Payroll\CompanyCost;
use App\Models\Payroll\PayrollDetail;

/**
 * Port dari SemartHris\Component\Salary\Service\StoreAsCompanyCost
 * (dan behavior storeDetail pada PayrollRepository): setiap rincian gaji
 * disalin menjadi beban perusahaan (company cost).
 */
class StoreAsCompanyCost
{
    public function store(PayrollDetail $payrollDetail): void
    {
        $companyCost = CompanyCost::firstOrNew([
            'payroll_id' => $payrollDetail->payroll_id,
            'component_id' => $payrollDetail->component_id,
        ]);
        $companyCost->benefit_value = $payrollDetail->benefit_value;

        $companyCost->save();
    }
}