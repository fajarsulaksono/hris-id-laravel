<?php

namespace App\Http\Controllers;

use App\Models\Attendance\Attendance;
use App\Models\Attendance\Leave;
use App\Models\Attendance\Overtime;
use App\Models\Employee\Employee;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'employeeCount' => Employee::count(),
            'attendanceCount' => Attendance::whereDate('attendance_date', today())->count(),
            'overtimeCount' => Overtime::whereMonth('overtime_date', now()->month)->count(),
            'leaveCount' => Leave::where('leave_date', '>=', today())->count(),
        ]);
    }
}