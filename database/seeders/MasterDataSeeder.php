<?php

namespace Database\Seeders;

use App\Enums\ContractType;
use App\Enums\ReasonType;
use App\Models\Company\Company;
use App\Models\Company\CompanyAddress;
use App\Models\Company\CompanyDepartment;
use App\Models\Company\Department;
use App\Models\Company\JobLevel;
use App\Models\Company\JobTitle;
use App\Models\Master\City;
use App\Models\Master\Contract;
use App\Models\Master\EducationalInstitute;
use App\Models\Master\EducationTitle;
use App\Models\Master\Holiday;
use App\Models\Master\Reason;
use App\Models\Master\Region;
use App\Models\Master\Skill;
use App\Models\Master\SkillGroup;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->education();
        $this->regionsAndCities();
        $this->skillGroups();
        $this->reasons();
        $this->holidays();
        $this->contracts();
        $this->companyStructures();
        $this->jobStructures();
    }

    private function education(): void
    {
        foreach (['S1' => 'SARJANA', 'S2' => 'MAGISTER', 'S3' => 'DOKTOR', 'D3' => 'DIPLOMA TIGA'] as $short => $name) {
            EducationTitle::firstOrCreate(['short_name' => $short], ['name' => $name]);
        }

        foreach (['UNIVERSITAS INDONESIA', 'INSTITUT TEKNOLOGI BANDUNG', 'UNIVERSITAS GADJAH MADA', 'SMK NEGERI 1 JAKARTA'] as $name) {
            EducationalInstitute::firstOrCreate(['name' => $name]);
        }
    }

    private function regionsAndCities(): void
    {
        $dki = Region::firstOrCreate(['code' => 'R01'], ['name' => 'DKI JAKARTA']);
        $jabar = Region::firstOrCreate(['code' => 'R02'], ['name' => 'JAWA BARAT']);
        $jatim = Region::firstOrCreate(['code' => 'R03'], ['name' => 'JAWA TIMUR']);

        $cities = [
            'JK01' => ['KOTA JAKARTA BARAT', $dki],
            'JK02' => ['KOTA JAKARTA TIMUR', $dki],
            'JB01' => ['KOTA BANDUNG', $jabar],
            'JB02' => ['KOTA BOGOR', $jabar],
            'JT01' => ['KOTA SURABAYA', $jatim],
        ];

        foreach ($cities as $code => [$name, $region]) {
            City::firstOrCreate(['code' => $code], ['name' => $name, 'region_id' => $region->getKey()]);
        }
    }

    private function skillGroups(): void
    {
        $root = SkillGroup::firstOrCreate(['name' => 'TEKNOLOGI']);
        $it = SkillGroup::firstOrCreate(['name' => 'TEKNOLOGI INFORMASI'], ['parent_id' => $root->getKey()]);
        $hr = SkillGroup::firstOrCreate(['name' => 'KEPEGAWAIAN'], ['parent_id' => $root->getKey()]);

        $skills = [
            [$it, 'PROGRAMMING'],
            [$it, 'INFRASTRUKTUR'],
            [$hr, 'REKRUTMEN'],
            [$hr, 'PENGEMBANGAN ORGANISASI'],
        ];

        foreach ($skills as [$group, $name]) {
            Skill::firstOrCreate(['name' => $name], ['skill_group_id' => $group->getKey()]);
        }
    }

    private function reasons(): void
    {
        $absent = [
            'S' => 'SAKIT',
            'I' => 'IZIN',
            'A' => 'ALPA',
            'D' => 'DINAS LUAR',
        ];

        foreach ($absent as $code => $name) {
            Reason::firstOrCreate(['type' => ReasonType::ABSENT->value, 'code' => $code], ['name' => $name]);
        }

        $leave = [
            'C' => 'CUTI TAHUNAN',
            'B' => 'CUTI BERSAMA',
            'M' => 'CUTI MELAHIRKAN',
            'P' => 'CUTI KARENA ALASAN PENTING',
        ];

        foreach ($leave as $code => $name) {
            Reason::firstOrCreate(['type' => ReasonType::LEAVE->value, 'code' => $code], ['name' => $name]);
        }
    }

    private function holidays(): void
    {
        $holidays = [
            '2026-01-01' => 'TAHUN BARU',
            '2026-08-17' => 'HARI KEMERDEKAAN RI',
            '2026-12-25' => 'HARI NATAL',
        ];

        foreach ($holidays as $date => $name) {
            Holiday::firstOrCreate(['holiday_date' => $date], ['name' => $name]);
        }
    }

    private function contracts(): void
    {
        $contracts = [
            [
                'type' => ContractType::PERMANENT,
                'letter_number' => 'KR/00001/P/2026',
                'subject' => 'KONTRAK KERJA PERMANEN',
                'description' => 'Kontrak kerja untuk karyawan tetap.',
                'start_date' => '2026-01-02',
                'end_date' => null,
                'signed_date' => '2026-01-01',
                'tags' => ['KPI', 'CORE'],
                'used' => true,
            ],
            [
                'type' => ContractType::TEMPORARY,
                'letter_number' => 'KR/00002/T/2026',
                'subject' => 'KONTRAK KERJA KONTRAK',
                'description' => 'Kontrak kerja untuk karyawan kontrak satu tahun.',
                'start_date' => '2026-01-02',
                'end_date' => '2026-12-31',
                'signed_date' => '2026-01-01',
                'tags' => ['CORE'],
                'used' => false,
            ],
            [
                'type' => ContractType::OUTSOURCE,
                'letter_number' => 'KR/00003/O/2026',
                'subject' => 'KONTRAK KERJA OUTSOURCE',
                'description' => null,
                'start_date' => '2026-02-01',
                'end_date' => '2026-07-31',
                'signed_date' => '2026-01-30',
                'tags' => null,
                'used' => false,
            ],
        ];

        foreach ($contracts as $contract) {
            Contract::firstOrCreate(['letter_number' => $contract['letter_number']], [
                'type' => $contract['type']->value,
                'subject' => $contract['subject'],
                'description' => $contract['description'],
                'start_date' => $contract['start_date'],
                'end_date' => $contract['end_date'],
                'signed_date' => $contract['signed_date'],
                'tags' => $contract['tags'],
                'used' => $contract['used'],
            ]);
        }
    }

    private function companyStructures(): void
    {
        $semart = Company::firstOrCreate(['code' => 'C001'], [
            'name' => 'PT SEMART SOLUTIONS',
            'birth_day' => '2015-03-15',
            'email' => 'hr@semart.id',
            'tax_number' => '01.234.567.8-042.000',
        ]);

        $consulting = Company::firstOrCreate(['code' => 'C002'], [
            'name' => 'PT SEMART KONSULTAN',
            'birth_day' => '2018-08-20',
            'email' => 'hr@semartkonsultan.id',
            'tax_number' => '01.987.654.3-042.000',
            'parent_id' => $semart->getKey(),
        ]);

        $dki = Region::where('code', 'R01')->first();
        $jakartaBarat = City::where('code', 'JK01')->first();

        CompanyAddress::firstOrCreate(
            ['company_id' => $semart->getKey()],
            [
                'address' => 'JL. JENDERAL SUDIRMAN NO. 12',
                'region_id' => $dki->getKey(),
                'city_id' => $jakartaBarat->getKey(),
                'postal_code' => '11480',
                'phone_number' => '021-5001234',
                'fax_number' => '021-5001235',
                'default_address' => true,
            ]
        );

        $departments = ['HR' => 'HUMAN RESOURCE DEVELOPMENT', 'FN' => 'FINANCE', 'IT' => 'INFORMATION TECHNOLOGY'];

        $it = null;
        foreach ($departments as $code => $name) {
            if ($code === 'IT') {
                $it = Department::firstOrCreate(['code' => $code], ['name' => $name]);
            } else {
                Department::firstOrCreate(['code' => $code], ['name' => $name]);
            }
        }

        Department::firstOrCreate(['code' => 'DV'], ['name' => 'DEVELOPMENT', 'parent_id' => $it->getKey()]);

        foreach (['HR', 'FN', 'IT', 'DV'] as $code) {
            $department = Department::where('code', $code)->first();

            CompanyDepartment::firstOrCreate([
                'company_id' => $semart->getKey(),
                'department_id' => $department->getKey(),
            ]);

            CompanyDepartment::firstOrCreate([
                'company_id' => $consulting->getKey(),
                'department_id' => $department->getKey(),
            ]);
        }
    }

    private function jobStructures(): void
    {
        $levels = ['GR' => 'GENERAL', 'ST' => 'STAFF', 'SP' => 'SUPERVISOR', 'MG' => 'MANAGER', 'DR' => 'DIREKTUR'];

        foreach ($levels as $code => $name) {
            JobLevel::firstOrCreate(['code' => $code], ['name' => $name]);
        }

        $titles = [
            'HM01' => ['HR STAFF', 'ST'],
            'HM02' => ['HR SUPERVISOR', 'SP'],
            'HM03' => ['HR MANAGER', 'MG'],
            'HM04' => ['DIREKTUR HR', 'DR'],
        ];

        foreach ($titles as $code => [$name, $levelCode]) {
            $level = JobLevel::where('code', $levelCode)->first();

            JobTitle::firstOrCreate(['code' => $code], ['name' => $name, 'job_level_id' => $level->getKey()]);
        }
    }
}