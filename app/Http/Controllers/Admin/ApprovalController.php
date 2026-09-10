<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\BaseController;
use App\Models\Attendance\Leave;
use App\Models\Attendance\Overtime;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApprovalController extends BaseController
{
    public function leaves(): View
    {
        $rows = Leave::query()
            ->with(['employee', 'reason', 'approvedBy'])
            ->orderBy('leave_date', 'desc')
            ->paginate((int) config('hris.record_per_page'));

        return view('admin.approvals.leaves', compact('rows'));
    }

    public function approveLeave(Leave $leave): RedirectResponse
    {
        abort_unless($leave->status === ApprovalStatus::PENDING, 422, 'Hanya permohonan yang menunggu yang bisa diproses.');

        $this->applyDecision($leave, ApprovalStatus::APPROVED);

        return back()->with('success', sprintf('Cuti/izin %s disetujui.', $leave->employee_name ?? 'karyawan'));
    }

    public function rejectLeave(Leave $leave): RedirectResponse
    {
        abort_unless($leave->status === ApprovalStatus::PENDING, 422, 'Hanya permohonan yang menunggu yang bisa diproses.');

        $this->applyDecision($leave, ApprovalStatus::REJECTED);

        return back()->with('success', sprintf('Cuti/izin %s ditolak.', $leave->employee_name ?? 'karyawan'));
    }

    public function overtimes(): View
    {
        $rows = Overtime::query()
            ->with(['employee', 'shiftment', 'approvedBy'])
            ->orderBy('overtime_date', 'desc')
            ->paginate((int) config('hris.record_per_page'));

        return view('admin.approvals.overtimes', compact('rows'));
    }

    public function approveOvertime(Overtime $overtime): RedirectResponse
    {
        abort_unless($overtime->status === ApprovalStatus::PENDING, 422, 'Hanya permohonan yang menunggu yang bisa diproses.');

        $this->applyDecision($overtime, ApprovalStatus::APPROVED);

        return back()->with('success', sprintf('Lembur %s disetujui.', $overtime->employee_name ?? 'karyawan'));
    }

    public function rejectOvertime(Overtime $overtime): RedirectResponse
    {
        abort_unless($overtime->status === ApprovalStatus::PENDING, 422, 'Hanya permohonan yang menunggu yang bisa diproses.');

        $this->applyDecision($overtime, ApprovalStatus::REJECTED);

        return back()->with('success', sprintf('Lembur %s ditolak.', $overtime->employee_name ?? 'karyawan'));
    }

    /**
     * Terapkan keputusan tanpa melepas event model (saveQuietly) agar observer
     * (mis. kalkulasi/auto-approve lembur) tidak membatalkan status manual.
     */
    private function applyDecision(Leave|Overtime $model, ApprovalStatus $status): void
    {
        $model->forceFill([
            'status' => $status,
            'approved_by_id' => auth()->id(),
        ])->saveQuietly();
    }
}
