<?php

namespace App\Policies;

use App\Domain\Employee\SupervisorChecker;
use App\Models\Employee\Employee;
use App\Support\Security;

class EmployeePolicy
{
    /**
     * HR (dan di atasnya) boleh mengelola semua karyawan.
     */
    public function before(Employee $user, string $ability): ?bool
    {
        $security = app(Security::class);
        $minRank = $security->menuRank('employee_menu');

        if ($minRank !== null && $security->userRank($user) >= $minRank) {
            return true;
        }

        return null;
    }

    /**
     * Karyawan hanya bisa melihat anak buahnya (rantai supervisor) dengan level berbeda.
     */
    public function view(Employee $user, Employee $employee): bool
    {
        return app(SupervisorChecker::class)->isAllowToSupervise($employee, $user);
    }

    public function update(Employee $user, Employee $employee): bool
    {
        return $this->view($user, $employee);
    }

    public function create(Employee $user): bool
    {
        return false;
    }

    public function delete(Employee $user, Employee $employee): bool
    {
        return false;
    }
}