<?php

namespace Database\Seeders;

use App\Enums\ContractType;
use App\Enums\Gender;
use App\Enums\IdentityType;
use App\Enums\MaritalStatus;
use App\Enums\RiskRatio;
use App\Enums\TaxGroup;
use App\Models\Employee\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoUserSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'password123';

    protected array $users = [
        // EMPLOYEE
        [
            'role' => 'EMPLOYEE',
            'code' => 'EMP001',
            'full_name' => 'Budi Santoso',
            'username' => 'budi.santoso',
            'email' => 'budi.santoso@example.test',
            'gender' => 'm',
            'identity_number' => '3174012509900001',
            'join_date' => '2020-03-01',
            'contract_type' => 'p',
        ],
        // HRSTAFF
        [
            'role' => 'HRSTAFF',
            'code' => 'HR001',
            'full_name' => 'Sari Wulandari',
            'username' => 'sari.wulandari',
            'email' => 'sari.wulandari@example.test',
            'gender' => 'f',
            'identity_number' => '3174012509900002',
            'join_date' => '2019-07-15',
            'contract_type' => 'p',
        ],
        // HRSUPERVISOR
        [
            'role' => 'HRSUPERVISOR',
            'code' => 'HR002',
            'full_name' => 'Dewi Lestari',
            'username' => 'dewi.lestari',
            'email' => 'dewi.lestari@example.test',
            'gender' => 'f',
            'identity_number' => '3174012509900003',
            'join_date' => '2018-01-10',
            'contract_type' => 'p',
        ],
        // HRMANAGER
        [
            'role' => 'HRMANAGER',
            'code' => 'HR003',
            'full_name' => 'Rina Kartika',
            'username' => 'rina.kartika',
            'email' => 'rina.kartika@example.test',
            'gender' => 'f',
            'identity_number' => '3174012509900004',
            'join_date' => '2016-05-20',
            'contract_type' => 'p',
        ],
        // HRGENERAL_MANAGER
        [
            'role' => 'HRGENERAL_MANAGER',
            'code' => 'HR004',
            'full_name' => 'Andi Prasetyo',
            'username' => 'andi.prasetyo',
            'email' => 'andi.prasetyo@example.test',
            'gender' => 'm',
            'identity_number' => '3174012509900005',
            'join_date' => '2015-09-01',
            'contract_type' => 'p',
        ],
        // HRDIRECTOR
        [
            'role' => 'HRDIRECTOR',
            'code' => 'DIR001',
            'full_name' => 'Sri Handayani',
            'username' => 'sri.handayani',
            'email' => 'sri.handayani@example.test',
            'gender' => 'f',
            'identity_number' => '3174012509900006',
            'join_date' => '2013-02-11',
            'contract_type' => 'p',
        ],
        // TOP_LEVEL_MANAGEMENT
        [
            'role' => 'TOP_LEVEL_MANAGEMENT',
            'code' => 'TOP001',
            'full_name' => 'Eko Wijaya',
            'username' => 'eko.wijaya',
            'email' => 'eko.wijaya@example.test',
            'gender' => 'm',
            'identity_number' => '3174012509900007',
            'join_date' => '2012-08-25',
            'contract_type' => 'p',
        ],
        // SUPER_ADMIN
        [
            'role' => 'SUPER_ADMIN',
            'code' => 'SA001',
            'full_name' => 'Agus Setiawan',
            'username' => 'agus.setiawan',
            'email' => 'agus.setiawan@example.test',
            'gender' => 'm',
            'identity_number' => '3174012509900008',
            'join_date' => '2010-01-01',
            'contract_type' => 'p',
        ],
    ];

    public function run(): void
    {
        foreach ($this->users as $data) {
            $role = $data['role'];
            $code = Str::upper($data['code']);

            $employee = Employee::query()
                ->where('username', $data['username'])
                ->orWhere('code', $code)
                ->first();

            if (!$employee) {
                $employee = new Employee();
            }

            $employee->code = $code;
            $employee->full_name = $data['full_name'];
            $employee->username = strtolower($data['username']);
            $employee->email = strtolower($data['email']);
            $employee->password = Hash::make(self::DEMO_PASSWORD);
            $employee->join_date = $data['join_date'];
            $employee->employee_status = ContractType::from($data['contract_type']);
            $employee->gender = Gender::from($data['gender']);
            $employee->date_of_birth = '1990-09-25';
            $employee->identity_number = $data['identity_number'];
            $employee->identity_type = IdentityType::ID_CARD;
            $employee->marital_status = MaritalStatus::SINGLE;
            $employee->leave_balance = 12;
            $employee->tax_group = TaxGroup::TK0;
            $employee->risk_ratio = RiskRatio::RISK_VERY_LOW;
            $employee->have_overtime_benefit = true;
            $employee->save();

            $employee->syncRoles($role);
        }
    }
}