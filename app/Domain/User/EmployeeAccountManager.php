<?php

namespace App\Domain\User;

use App\Models\Employee\Employee;
use Illuminate\Support\Facades\Hash;

class EmployeeAccountManager
{
    public function __construct(protected UsernameGenerator $usernameGenerator) {}

    /**
     * Username unik dari nama lengkap. Tambahkan angka bila sudah dipakai.
     */
    public function generateUniqueUsername(Employee $employee): string
    {
        $base = $this->usernameGenerator->generate($employee);
        $username = $base;
        $suffix = 2;

        while (Employee::withTrashed()->where('username', $username)->exists()) {
            $username = $base.$suffix++;
        }

        return $username;
    }

    /**
     * Kode otomatis berdasarkan sufik numerik tertinggi (mis. EMP001, EMP002, ...).
     */
    public function generateEmployeeCode(): string
    {
        $max = 0;

        foreach (Employee::withTrashed()->where('code', 'like', 'EMP%')->pluck('code') as $code) {
            if (preg_match('/^EMP0*(\d+)$/i', (string) $code, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return sprintf('EMP%03d', $max + 1);
    }

    public function defaultPassword(): string
    {
        return Hash::make((string) config('hris.default_password'));
    }
}
