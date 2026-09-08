<?php

namespace Database\Seeders;

use App\Enums\SalaryState;
use App\Models\Payroll\SalaryComponent;
use Illuminate\Database\Seeder;

/**
 * Port data/salary_component.yaml (SemartHris) menjadi komponen gaji default.
 */
class SalaryComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            ['GP', 'GAJI POKOK', 'p', true],
            ['TJ', 'TUNJANGAN JABATAN', 'p', true],
            ['UM', 'UANG MAKAN', 'p', false],
            ['UT', 'UANG TRANSPORT', 'p', false],
            ['OT', 'TUNJANGAN LEMBUR', 'p', false],
            ['JKK', 'TUNJANGAN BPJS JKK PERUSAHAAN', 'p', true],
            ['JKM', 'TUNJANGAN BPJS JKM PERUSAHAAN', 'p', true],
            ['JHTP', 'TUNJANGAN BPJS JTH', 'p', true],
            ['JHTM', 'POTONGAN BPJS JTH', 'm', true],
            ['JHTC', 'TUNJANGAN BPJS JTH PERUSAHAAN', 'p', true],
            ['JPP', 'TUNJANGAN BPJS JP', 'p', true],
            ['JPM', 'POTONGAN BPJS JP', 'm', true],
            ['JPC', 'TUNJANGAN BPJS JP PERUSAHAAN', 'p', true],
            ['PPH21P', 'TUNJANGAN PAJAK PPH21', 'p', true],
            ['PPH21M', 'POTONGAN PAJAK PPH21', 'm', true],
            ['PL', 'POTONGAN LAIN-LAIN', 'm', false],
        ];

        foreach ($components as [$code, $name, $state, $fixed]) {
            SalaryComponent::firstOrCreate(['code' => $code], [
                'name' => $name,
                'state' => SalaryState::from($state),
                'fixed' => $fixed,
            ]);
        }
    }
}