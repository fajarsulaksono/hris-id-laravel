<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Salary\Processor\InvalidPayrollPeriodException;
use App\Domain\Salary\Service\PayrollProcessor;
use App\Domain\Tax\Service\TaxProcessor as TaxProcessorService;
use App\Http\Controllers\BaseController;
use App\Models\Company\Company;
use App\Models\Employee\Employee;
use App\Models\Payroll\CompanyCost;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollDetail;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Tax\Tax;
use App\Support\MasterModules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayrollController extends BaseController
{
    public function processForm(Request $request): View
    {
        return view('admin.payroll.process', [
            'module' => MasterModules::get('payrolls'),
            'formAction' => route('admin.payroll.payrolls.process'),
            'backUrl' => route('admin.payroll.payrolls.index'),
            'companies' => Company::query()->orderBy('name')->get(),
            'year' => (int) date('Y'),
            'month' => (int) date('n'),
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2099'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'company_id' => ['nullable', 'exists:companies,id'],
        ]);

        $year = (int) $data['year'];
        $month = (int) $data['month'];
        $date = \DateTimeImmutable::createFromFormat('Y-n-d', sprintf('%s-%s-01', $year, $month));

        if ($month > (int) date('n')) {
            return redirect()->route('admin.payroll.payrolls.process')
                ->withErrors(['period' => 'Periode tidak boleh melewati bulan berjalan.']);
        }

        $employees = Employee::query()
            ->when(! empty($data['company_id']), fn ($query) => $query->where('company_id', $data['company_id']))
            ->orderBy('code')
            ->get();

        $processor = app(PayrollProcessor::class);

        try {
            DB::transaction(function () use ($employees, $date, $processor) {
                foreach ($employees as $employee) {
                    if ($employee->isResign()) {
                        continue;
                    }

                    $processor->process($employee, $date);
                }
            });
        } catch (InvalidPayrollPeriodException $exception) {
            return redirect()->route('admin.payroll.payrolls.process')
                ->withErrors(['period' => $exception->getMessage()]);
        }

        return redirect()->route('admin.payroll.payrolls.process')
            ->with('success', sprintf('Proses penggajian %s-%s selesai untuk %d karyawan.', str_pad((string) $month, 2, '0', STR_PAD_LEFT), $year, $employees->count()));
    }

    public function taxForm(Request $request): View
    {
        return view('admin.payroll.tax', [
            'module' => MasterModules::get('taxes'),
            'formAction' => route('admin.payroll.payrolls.tax'),
            'backUrl' => route('admin.payroll.taxes.index'),
            'companies' => Company::query()->orderBy('name')->get(),
            'year' => (int) date('Y'),
            'month' => (int) date('n'),
        ]);
    }

    public function processTax(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2099'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'company_id' => ['nullable', 'exists:companies,id'],
        ]);

        $year = (int) $data['year'];
        $month = (int) $data['month'];
        $date = \DateTimeImmutable::createFromFormat('Y-n-d', sprintf('%s-%s-01', $year, $month));

        if ($month > (int) date('n')) {
            return redirect()->route('admin.payroll.payrolls.tax')
                ->withErrors(['period' => 'Periode tidak boleh melewati bulan berjalan.']);
        }

        $employees = Employee::query()
            ->when(! empty($data['company_id']), fn ($query) => $query->where('company_id', $data['company_id']))
            ->orderBy('code')
            ->get();

        $processor = app(TaxProcessorService::class);

        DB::transaction(function () use ($employees, $date, $processor) {
            foreach ($employees as $employee) {
                if ($employee->isResign()) {
                    continue;
                }

                $period = PayrollPeriod::query()
                    ->where('company_id', $employee->company_id)
                    ->where('year', (int) $date->format('Y'))
                    ->where('month', (int) $date->format('n'))
                    ->first();

                if (! $period || $period->closed) {
                    throw new InvalidPayrollPeriodException($date);
                }

                $processor->process($employee, $period);
            }
        });

        return redirect()->route('admin.payroll.payrolls.tax')
            ->with('success', sprintf('Proses pajak %s-%s selesai untuk %d karyawan.', str_pad((string) $month, 2, '0', STR_PAD_LEFT), $year, $employees->count()));
    }

    public function detail(Request $request, string $id): View
    {
        $payroll = Payroll::with(['employee', 'period.company', 'details.component'])->findOrFail($id);

        $details = $payroll->details->sortByDesc(fn (PayrollDetail $detail) => $detail->benefit_value);

        $tax = Tax::query()
            ->where('period_id', $payroll->period_id)
            ->where('employee_id', $payroll->employee_id)
            ->first();

        $costs = CompanyCost::query()
            ->where('payroll_id', $payroll->getKey())
            ->with('component')
            ->orderBy('component_id')
            ->get();

        return view('admin.payroll.detail', [
            'module' => MasterModules::get('payrolls'),
            'payroll' => $payroll,
            'details' => $details,
            'tax' => $tax,
            'costs' => $costs,
            'backUrl' => route('admin.payroll.payrolls.index'),
        ]);
    }
}