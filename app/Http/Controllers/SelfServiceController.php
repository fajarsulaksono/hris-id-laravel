<?php

namespace App\Http\Controllers;

use App\Enums\ReasonType;
use App\Enums\SalaryState;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Leave;
use App\Models\Employee\Employee;
use App\Models\Master\Reason;
use App\Models\Payroll\CompanyCost;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollDetail;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SelfServiceController extends Controller
{
    public function profile(): View
    {
        /** @var Employee $employee */
        $employee = auth()->user();
        $employee->loadMissing(['company', 'department', 'jobTitle', 'supervisor', 'addresses.city', 'families', 'educations.educationTitle', 'skills.skill']);

        return view('my.profile', compact('employee'));
    }

    public function attendance(): View
    {
        $rows = Attendance::query()
            ->with('shiftment')
            ->where('employee_id', auth()->id())
            ->orderBy('attendance_date', 'desc')
            ->paginate(15);

        return view('my.attendance', compact('rows'));
    }

    public function leaves(): View
    {
        /** @var Employee $employee */
        $employee = auth()->user();

        $rows = Leave::query()
            ->with('reason')
            ->where('employee_id', $employee->getKey())
            ->orderBy('leave_date', 'desc')
            ->paginate(15);

        $reasons = Reason::query()
            ->where('type', ReasonType::LEAVE)
            ->orderBy('name')
            ->get();

        return view('my.leaves', [
            'rows' => $rows,
            'reasons' => $reasons,
            'balance' => (int) $employee->leave_balance,
        ]);
    }

    public function storeLeave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'leave_date' => ['required', 'date'],
            'reason_id' => ['required', 'exists:absent_reasons,id'],
            'amount' => ['required', 'integer', 'min:1', 'max:31'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var Employee $employee */
        $employee = auth()->user();

        Leave::create($data + ['employee_id' => $employee->getKey()]);

        return back()->with('success', 'Permohonan cuti/izin berhasil diajukan.');
    }

    public function payrolls(): View
    {
        $rows = Payroll::query()
            ->with('period.company')
            ->where('employee_id', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('my.payrolls', compact('rows'));
    }

    public function payrollPdf(Payroll $payroll)
    {
        abort_unless($payroll->employee_id === auth()->id(), 403);

        $details = $payroll->details
            ->sortBy(fn (PayrollDetail $detail) => $detail->component?->state === SalaryState::PLUS ? 0 : 1);

        $costs = CompanyCost::query()
            ->where('payroll_id', $payroll->getKey())
            ->with('component')
            ->get();

        $pdf = PdfFacade::loadView('admin.payroll.slip-pdf', [
            'payroll' => $payroll,
            'details' => $details,
            'costs' => $costs,
        ]);

        $period = $payroll->period?->display ?? 'slip';
        $code = $payroll->employee?->code ?? 'slip';

        return $pdf->download("slip-{$period}-{$code}.pdf");
    }
}
