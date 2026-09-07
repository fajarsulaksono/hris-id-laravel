<?php

namespace App\Http\Middleware;

use App\Models\Employee\Employee;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetEmployeeContext
{
    /**
     * Session sticky karyawan (pengganti session `employeeId` Symfony):
     * saat user memilih Karyawan, ID disimpan di session sehingga CRUD turunan
     * (alamat, penempatan, riwayat karir) otomatis tersaring.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $candidate = $request->route('employee') ?? $request->input('employee_id');

        if ($candidate !== null) {
            if (Employee::whereKey($candidate)->exists()) {
                $request->session()->put('hris.employee_id', $candidate);
            }
        }

        $employeeId = $request->session()->get('hris.employee_id');

        View::share('currentEmployeeId', $employeeId);
        View::share('currentEmployee', $employeeId ? Employee::find($employeeId) : null);

        return $next($request);
    }
}