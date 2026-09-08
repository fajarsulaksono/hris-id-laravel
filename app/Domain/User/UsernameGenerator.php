<?php

namespace App\Domain\User;

use App\Models\Employee\Employee;

class UsernameGenerator
{
    public function generate(Employee $employee): string
    {
        $fullName = strtolower((string) $employee->full_name);
        $parts = array_values(array_filter(preg_split('/\s+/', trim($fullName))));

        if (count($parts) === 0) {
            return 'user';
        }

        $firstName = $parts[0];
        $lastName = count($parts) > 1 ? end($parts) : $firstName;

        return sprintf('%s.%s', $firstName, $lastName);
    }
}