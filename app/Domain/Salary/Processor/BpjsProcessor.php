<?php

namespace App\Domain\Salary\Processor;

use App\Domain\Salary\SalaryProcessorInterface;
use App\Domain\Salary\Service\StoreAsCompanyCost;
use App\Models\Employee\Employee;
use App\Models\Payroll\CompanyCost;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollDetail;
use App\Models\Payroll\SalaryComponent;

/**
 * Port dari SemartHris\Component\Salary\Processor\BpjsProcessor:
 * menghitung iuran BPJS Ketenagakerjaan (JKK, JKM, JHT, JP).
 *
 * @see http://www.pasienbpjs.com/2017/01/cara-menghitung-iuran-bpjs-ketenagakerjaan.html
 */
class BpjsProcessor implements SalaryProcessorInterface
{
    private const JKM_RATE = 0.003;
    private const JHT_COMPANY_RATE = 0.037;
    private const JHT_EMPLOYEE_RATE = 0.02;
    private const JP_COMPANY_RATE = 0.02;
    private const JP_EMPLOYEE_RATE = 0.01;

    public function __construct(
        private StoreAsCompanyCost $storeAsCompanyCost,
        private string $jkkCode,
        private string $jkmCode,
        private string $jhtPlusCode,
        private string $jhtMinusCode,
        private string $jhtCompanyCode,
        private string $jpPlusCode,
        private string $jpMinusCode,
        private string $jpCompanyCode,
    ) {
    }

    public function process(Payroll $payroll, Employee $employee, \DateTimeInterface $date, float $fixedSalary): float
    {
        $this->processJkk($payroll, $employee, $fixedSalary);
        $this->processJkm($payroll, $fixedSalary);
        $this->processJht($payroll, $fixedSalary);
        $this->processJp($payroll, $fixedSalary);

        return 0.0;
    }

    private function processJkk(Payroll $payroll, Employee $employee, float $fixedSalary): void
    {
        $jkkComponent = $this->component($this->jkkCode, 'JKK benefit code is not valid.');

        $jkk = round($employee->getRiskRatioValue() * $fixedSalary, 0, PHP_ROUND_HALF_DOWN);

        $this->storeCompanyCost($payroll, $jkkComponent, (string) $jkk);
    }

    private function processJkm(Payroll $payroll, float $fixedSalary): void
    {
        $jkmComponent = $this->component($this->jkmCode, 'JKM benefit code is not valid.');

        $jkm = round(self::JKM_RATE * $fixedSalary, 0, PHP_ROUND_HALF_DOWN);

        $this->storeCompanyCost($payroll, $jkmComponent, (string) $jkm);
    }

    private function processJht(Payroll $payroll, float $fixedSalary): void
    {
        $jhtCompany = $this->component($this->jhtCompanyCode, 'JHT company benefit code is not valid.');

        $jhtc = round(self::JHT_COMPANY_RATE * $fixedSalary, 0, PHP_ROUND_HALF_DOWN);
        $jht = round(self::JHT_EMPLOYEE_RATE * $fixedSalary, 0, PHP_ROUND_HALF_DOWN);

        $this->storeCompanyCost($payroll, $jhtCompany, (string) $jhtc);

        $jhtEmployeePlus = SalaryComponent::query()->where('code', $this->jhtPlusCode)->first();
        if ($jhtEmployeePlus) {
            $this->storeDetail($payroll, $jhtEmployeePlus, (string) $jht);
        }

        $jhtEmployeeMinus = SalaryComponent::query()->where('code', $this->jhtMinusCode)->first();
        if ($jhtEmployeeMinus) {
            $this->storeDetail($payroll, $jhtEmployeeMinus, (string) $jht);
        }
    }

    private function processJp(Payroll $payroll, float $fixedSalary): void
    {
        $jpCompany = $this->component($this->jpCompanyCode, 'JP company benefit code is not valid.');

        $jpc = round(self::JP_COMPANY_RATE * $fixedSalary, 0, PHP_ROUND_HALF_DOWN);
        $jp = round(self::JP_EMPLOYEE_RATE * $fixedSalary, 0, PHP_ROUND_HALF_DOWN);

        $this->storeCompanyCost($payroll, $jpCompany, (string) $jpc);

        $jpEmployeePlus = SalaryComponent::query()->where('code', $this->jpPlusCode)->first();
        if ($jpEmployeePlus) {
            $this->storeDetail($payroll, $jpEmployeePlus, (string) $jp);
        }

        $jpEmployeeMinus = SalaryComponent::query()->where('code', $this->jpMinusCode)->first();
        if ($jpEmployeeMinus) {
            $this->storeDetail($payroll, $jpEmployeeMinus, (string) $jp);
        }
    }

    private function storeDetail(Payroll $payroll, SalaryComponent $component, string $benefitValue): void
    {
        $payrollDetail = PayrollDetail::firstOrNew([
            'payroll_id' => $payroll->getKey(),
            'component_id' => $component->getKey(),
        ]);
        $payrollDetail->benefit_value = $benefitValue;

        $this->storeAsCompanyCost->store($payrollDetail);
        $payrollDetail->save();
    }

    private function storeCompanyCost(Payroll $payroll, SalaryComponent $component, string $benefitValue): void
    {
        $companyCost = CompanyCost::firstOrNew([
            'payroll_id' => $payroll->getKey(),
            'component_id' => $component->getKey(),
        ]);
        $companyCost->benefit_value = $benefitValue;

        $companyCost->save();
    }

    private function component(string $code, string $message): SalaryComponent
    {
        return SalaryComponent::query()
            ->where('code', $code)
            ->firstOr(fn () => throw new \RuntimeException($message));
    }
}