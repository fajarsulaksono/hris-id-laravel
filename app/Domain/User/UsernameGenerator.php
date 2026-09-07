<?php

namespace App\Domain\User;

use App\Models\Employee\Employee;

class UsernameGenerator
{
    public function generate(Employee $employee): string
    {
        $fullName = strtolower((string) $employee->full_name);
        $parts = preg_split('/\s+/', trim($fullName));

        $firstName = $parts[0] ?? '';
        $lastName = count($parts) > 1 ? end($parts) : '';

        return sprintf('%s.%s', $firstName, $lastName);
    }
}