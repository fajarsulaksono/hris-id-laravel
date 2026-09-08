<?php

namespace App\Http\Controllers;

use App\Models\Attendance\Attendance;
use App\Models\Attendance\Leave;
use App\Models\Attendance\Overtime;
use App\Models\Employee\Employee;
use App\Models\Payroll\Payroll;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function index(): View
    {
        $user = auth()->user();

        return view('dashboard', [
            'employeeCount' => Employee::count(),
            'attendanceCount' => Attendance::whereDate('attendance_date', today())->count(),
            'overtimeCount' => Overtime::whereMonth('overtime_date', now()->month)->count(),
            'leaveCount' => Leave::where('leave_date', '>=', today())->count(),
            'departmentChart' => $this->departmentChart(),
            'attendanceChart' => $this->attendanceChart(),
            'payrollChart' => $user->can('view_payroll') ? $this->payrollChart() : null,
        ]);
    }

    /**
     * Donut: komposisi karyawan per departemen.
     *
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    protected function departmentChart(): array
    {
        $rows = Employee::query()
            ->leftJoin('departments', 'departments.id', 'employees.department_id')
            ->selectRaw('COALESCE(NULLIF(departments.name, ""), "Tanpa Departemen") as label')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('total')->map(fn ($total) => (int) $total)->all(),
        ];
    }

    /**
     * Area: tren kehadiran (hadir vs absen) 12 bulan terakhir.
     *
     * @return array{labels: array<int, string>, present: array<int, int>, absent: array<int, int>}
     */
    protected function attendanceChart(): array
    {
        $start = now()->startOfMonth()->subMonths(11);
        $end = now()->endOfMonth();

        $index = [];
        $labels = [];
        $present = [];
        $absent = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $index[$month->format('Y-m')] = $i;
            $labels[] = $this->monthLabel($month);
            $present[] = 0;
            $absent[] = 0;
        }

        Attendance::query()
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get(['attendance_date', 'absent'])
            ->each(function (Attendance $attendance) use ($index, &$present, &$absent): void {
                $key = $attendance->attendance_date?->format('Y-m');
                $i = $key !== null ? ($index[$key] ?? null) : null;

                if ($i === null) {
                    return;
                }

                if ($attendance->absent) {
                    $absent[$i]++;
                } else {
                    $present[$i]++;
                }
            });

        return [
            'labels' => $labels,
            'present' => $present,
            'absent' => $absent,
        ];
    }

    /**
     * Bar: tren total take home pay per periode (12 periode terakhir).
     *
     * @return array{labels: array<int, string>, values: array<int, float>}|null
     */
    protected function payrollChart(): ?array
    {
        $totals = [];

        Payroll::query()
            ->with('period:id,company_id,year,month')
            ->get(['period_id', 'take_home_pay'])
            ->each(function (Payroll $payroll) use (&$totals): void {
                $label = $payroll->period?->display;

                if ($label === null) {
                    return;
                }

                $totals[$label] = ($totals[$label] ?? 0) + (float) $payroll->take_home_pay;
            });

        if ($totals === []) {
            return null;
        }

        uksort($totals, fn (string $a, string $b) => strcmp($a, $b));

        $labels = [];
        $values = [];

        foreach (array_slice($totals, -12, 12, true) as $label => $total) {
            $labels[] = $this->monthLabel(Carbon::createFromFormat('Y-m', $label));
            $values[] = round($total);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    protected function monthLabel(Carbon $month): string
    {
        return self::MONTHS_SHORT[$month->month - 1].' '.substr((string) $month->year, -2);
    }
}
