<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Attendance\AttendanceProcessor;
use App\Domain\Attendance\AttendanceSummaryCalculator;
use App\Domain\Attendance\AttendanceImporter;
use App\Domain\Attendance\InvalidAttendancePeriodException;
use App\Domain\Attendance\PeriodValidator;
use App\Http\Controllers\BaseController;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Employee\Employee;
use App\Models\Master\Holiday;
use App\Support\MasterModules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use League\Csv\Reader;

class AttendanceController extends BaseController
{
    public function uploadForm(Request $request): View
    {
        return view('admin.attendance.upload', [
            'module' => MasterModules::get('attendances'),
            'formAction' => route('admin.attendance.attendances.upload'),
            'backUrl' => route('admin.attendance.attendances.index'),
        ]);
    }

    public function storeUpload(Request $request, AttendanceImporter $importer): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $reader = Reader::createFromString((string) $data['file']->get());
        $reader->setHeaderOffset(0);

        $importer->import($reader->getRecords());

        return redirect()->route('admin.attendance.attendances.upload')
            ->with('success', 'Data absensi dari CSV berhasil diimpor.');
    }

    public function processForm(Request $request): View
    {
        return view('admin.attendance.process', [
            'module' => MasterModules::get('attendances'),
            'formAction' => route('admin.attendance.attendances.process'),
            'backUrl' => route('admin.attendance.attendances.index'),
            'year' => (int) date('Y'),
            'month' => (int) date('n'),
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2099'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        try {
            $period = app(PeriodValidator::class)->validate((int) $data['year'], (int) $data['month']);
        } catch (InvalidAttendancePeriodException $exception) {
            return redirect()->route('admin.attendance.attendances.process')
                ->withErrors(['period' => $exception->getMessage()]);
        }

        $employees = Employee::query()->orderBy('code')->get();

        $attendanceProcessor = app(AttendanceProcessor::class);
        $summaryCalculator = app(AttendanceSummaryCalculator::class);

        DB::transaction(function () use ($employees, $period, $attendanceProcessor, $summaryCalculator) {
            foreach ($employees as $employee) {
                // Clone agar mutasi in-place (processPartialMonth) tidak menggeser
                // periode untuk karyawan berikutnya.
                $attendanceProcessor->process($employee, (clone $period));
                $summaryCalculator->calculate($employee, (clone $period));
            }
        });

        return redirect()->route('admin.attendance.attendances.process')
            ->with('success', sprintf('Proses bulanan absensi %s-%s selesai untuk %d karyawan.', $period->format('m'), $period->format('Y'), $employees->count()));
    }

    public function recap(Request $request): View
    {
        $year = (int) request()->input('year', date('Y'));
        $month = (int) request()->input('month', date('n'));

        $summaries = AttendanceSummary::query()
            ->with('employee')
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('employee_id')
            ->get();

        $holidays = Holiday::query()
            ->whereBetween('holiday_date', [sprintf('%s-%s-01', $year, str_pad((string) $month, 2, '0', STR_PAD_LEFT)), sprintf('%s-%s-31', $year, str_pad((string) $month, 2, '0', STR_PAD_LEFT))])
            ->pluck('holiday_date')
            ->map(fn ($date) => $date?->format('Y-m-d'))
            ->filter()
            ->all();

        $offDays = (array) config('hris.offday_per_week');

        return view('admin.attendance.recap', [
            'summaries' => $summaries,
            'holidays' => $holidays,
            'offDays' => $offDays,
            'year' => $year,
            'month' => $month,
        ]);
    }
}