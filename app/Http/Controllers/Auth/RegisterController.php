<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ContractType;
use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Employee\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'identity_number' => ['required', 'string', 'max:27'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees,email'],
            'username' => ['required', 'string', 'max:64', 'unique:employees,username'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $employee = Employee::create([
            'code' => $this->nextEmployeeCode(),
            'full_name' => $data['full_name'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => Gender::from($data['gender'] === 'male' ? 'm' : 'f'),
            'identity_number' => $data['identity_number'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'join_date' => now()->toDateString(),
            'employee_status' => ContractType::TEMPORARY,
        ]);

        $employee->assignRole('EMPLOYEE');

        Auth::login($employee);

        return redirect()->route('dashboard');
    }

    private function nextEmployeeCode(): string
    {
        $last = Employee::withTrashed()->orderBy('created_at', 'desc')->value('code');

        if (!$last) {
            return 'EMP001';
        }

        $number = (int) preg_replace('/\D/', '', $last) + 1;

        return sprintf('EMP%03d', $number);
    }
}