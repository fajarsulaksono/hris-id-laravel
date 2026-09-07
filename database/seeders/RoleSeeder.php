<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public const ROLES = [
        'EMPLOYEE',
        'HRSTAFF',
        'HRSUPERVISOR',
        'HRMANAGER',
        'HRGENERAL_MANAGER',
        'HRDIRECTOR',
        'TOP_LEVEL_MANAGEMENT',
        'SUPER_ADMIN',
    ];

    public function run(): void
    {
        foreach (self::ROLES as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}