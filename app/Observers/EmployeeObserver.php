<?php

namespace App\Observers;

use App\Domain\User\EmployeeAccountManager;
use App\Models\Employee\Employee;
use Spatie\Permission\Models\Role;

class EmployeeObserver
{
    public function __construct(protected EmployeeAccountManager $accountManager) {}

    /**
     * Port GenerateUsernameSubscriber + UpdateJoinDateFromContractSubscriber
     * dari SemartHris: default username & password, join date dari kontrak.
     */
    public function creating(Employee $employee): void
    {
        if (empty($employee->code)) {
            $employee->code = $this->accountManager->generateEmployeeCode();
        }

        if (empty($employee->username)) {
            $employee->username = $this->accountManager->generateUniqueUsername($employee);
        }

        if (empty($employee->password)) {
            $employee->password = $this->accountManager->defaultPassword();
        }

        if (empty($employee->join_date) && $employee->contract?->start_date) {
            $employee->join_date = $employee->contract->start_date;
        }
    }

    /**
     * Role default untuk karyawan baru yang belum punya role.
     */
    public function created(Employee $employee): void
    {
        if ($employee->roles->isNotEmpty()) {
            return;
        }

        $role = Role::query()->where('name', Employee::DEFAULT_ROLE)->first();

        if ($role) {
            $employee->assignRole($role);
        }
    }
}
