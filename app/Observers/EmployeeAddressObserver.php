<?php

namespace App\Observers;

use App\Models\Employee\EmployeeAddress;

class EmployeeAddressObserver
{
    /**
     * Pastikan hanya satu alamat utama per karyawan (DefaultAddressChecker).
     */
    public function saving(EmployeeAddress $address): void
    {
        if (! $address->default_address || ! $address->employee_id) {
            return;
        }

        $this->others($address)->update(['default_address' => false]);
    }

    /**
     * Sinkronkan employee.address_id ke alamat utama.
     */
    public function saved(EmployeeAddress $address): void
    {
        if (! $address->employee_id) {
            return;
        }

        $employee = $address->employee;

        if ($employee === null) {
            return;
        }

        if ($address->default_address) {
            if ($employee->address_id !== $address->getKey()) {
                $employee->address_id = $address->getKey();
                $employee->save();
            }

            return;
        }

        if ($employee->address_id === $address->getKey()) {
            $remain = EmployeeAddress::query()
                ->where('employee_id', $address->employee_id)
                ->where('default_address', true)
                ->orderBy('updated_at', 'desc')
                ->first();

            $employee->address_id = $remain?->getKey();
            $employee->save();
        }
    }

    /**
     * Saat alamat utama dihapus, pilih alamat lain sebagai default (setRandomDefault).
     */
    public function deleted(EmployeeAddress $address): void
    {
        $employee = $address->employee;

        if ($employee === null || $employee->address_id !== $address->getKey()) {
            return;
        }

        $remain = EmployeeAddress::query()
            ->where('employee_id', $address->employee_id)
            ->where('default_address', true)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($remain) {
            $remain->default_address = true;
            $remain->save();
        }

        $employee->address_id = $remain?->getKey();
        $employee->save();
    }

    protected function others(EmployeeAddress $address)
    {
        return EmployeeAddress::query()
            ->where('employee_id', $address->employee_id)
            ->whereKeyNot($address->getKey());
    }
}