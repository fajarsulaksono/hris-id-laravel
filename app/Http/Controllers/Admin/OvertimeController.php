<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Attendance\InvalidAttendancePeriodException;
use App\Domain\Attendance\PeriodValidator;
use App\Domain\Overtime\OvertimeImporter;
use App\Domain\Overtime\OvertimeProcessor;
use App\Http\Controllers\BaseController;
use App\Models\Employee\Employee;
use App\Support\MasterModules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use League\Csv\Reader;

class OvertimeController extends BaseController
{
    public function uploadForm(Request $request): View
    {
        return view('admin.overtime.upload', [
            'module' => MasterModules::get('overtimes'),
            'formAction' => route('admin.overtime.overtimes.upload'),
            'backUrl' => route('admin.overtime.overtimes.index'),
        ]);
    }

    public function storeUpload(Request $request, OvertimeImporter $importer): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $reader = Reader::createFromString((string) $data['file']->get());
        $reader->setHeaderOffset(0);

        $importer->import($reader->getRecords());

        return redirect()->route('admin.overtime.overtimes.upload')
            ->with('success', 'Data lembur dari CSV berhasil diimpor.');
    }

    public function processForm(Request $request): View
    {
        return view('admin.overtime.process', [
            'module' => MasterModules::get('overtimes'),
            'formAction' => route('admin.overtime.overtimes.process'),
            'backUrl' => route('admin.overtime.overtimes.index'),
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
            return redirect()->route('admin.overtime.overtimes.process')
                ->withErrors(['period' => $exception->getMessage()]);
        }

        $employees = Employee::query()->orderBy('code')->get();
        $processor = app(OvertimeProcessor::class);

        DB::transaction(function () use ($employees, $period, $processor) {
            foreach ($employees as $employee) {
                $processor->process($employee, $period);
            }
        });

        return redirect()->route('admin.overtime.overtimes.process')
            ->with('success', sprintf('Proses bulanan lembur %s-%s selesai untuk %d karyawan.', $period->format('m'), $period->format('Y'), $employees->count()));
    }
}