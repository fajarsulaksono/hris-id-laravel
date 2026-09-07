<?php

namespace App\Support;

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

final class MasterModules
{
    /**
     * Daftar modul master + organisasi.
     * Kunci = slug rute; `menu` menentukan grup (dan hak akses) di sidebar.
     *
     * @return array<string, array>
     */
    public static function all(): array
    {
        return [
            'educational-institutes' => self::module(
                title: 'Lembaga Pendidikan',
                menu: 'master',
                model: EducationalInstitute::class,
                columns: [
                    ['data' => 'name', 'title' => 'Nama'],
                ],
                searchable: ['name'],
                fields: [
                    self::field('name', 'Nama', 'text', required: true, unique: true),
                ],
            ),

            'education-titles' => self::module(
                title: 'Gelar Pendidikan',
                menu: 'master',
                model: EducationTitle::class,
                searchable: ['short_name', 'name'],
                fields: [
                    self::field('name', 'Nama Gelar', 'text', required: true, upcase: true),
                    self::field('short_name', 'Singkatan', 'text', max: 5, required: true, upcase: true),
                ],
            ),

            'skill-groups' => self::module(
                title: 'Kelompok Keahlian',
                menu: 'master',
                model: SkillGroup::class,
                columns: [
                    ['data' => 'parent_name', 'title' => 'Induk'],
                    ['data' => 'name', 'title' => 'Nama'],
                ],
                searchable: ['name'],
                fields: [
                    self::field('name', 'Nama', 'text', required: true),
                    ['name' => 'parent_id', 'label' => 'Induk', 'type' => 'select', 'model' => SkillGroup::class,
                        'text' => 'name', 'nullable' => true],
                ],
            ),

            'skills' => self::module(
                title: 'Keahlian',
                menu: 'master',
                model: Skill::class,
                columns: [
                    ['data' => 'skill_group_name', 'title' => 'Kelompok'],
                    ['data' => 'name', 'title' => 'Nama'],
                ],
                searchable: ['name'],
                fields: [
                    self::field('name', 'Nama', 'text', required: true),
                    ['name' => 'skill_group_id', 'label' => 'Kelompok', 'type' => 'select',
                        'model' => SkillGroup::class, 'text' => 'name', 'required' => true],
                ],
            ),

            'regions' => self::module(
                title: 'Propinsi',
                menu: 'master',
                model: Region::class,
                searchable: ['code', 'name'],
                fields: [
                    self::field('code', 'Kode', 'text', max: 7, required: true, upcase: true, unique: true),
                    self::field('name', 'Nama', 'text', required: true, upcase: true),
                ],
            ),

            'cities' => self::module(
                title: 'Kota',
                menu: 'master',
                model: City::class,
                columns: [
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'region_name', 'title' => 'Propinsi'],
                    ['data' => 'name', 'title' => 'Nama'],
                ],
                searchable: ['code', 'name'],
                fields: [
                    ['name' => 'region_id', 'label' => 'Propinsi', 'type' => 'select',
                        'model' => Region::class, 'text' => 'display', 'required' => true],
                    self::field('code', 'Kode', 'text', max: 7, required: true, upcase: true, unique: true),
                    self::field('name', 'Nama', 'text', required: true, upcase: true),
                ],
            ),

            'holidays' => self::module(
                title: 'Hari Libur',
                menu: 'master',
                model: Holiday::class,
                fields: [
                    self::field('holiday_date', 'Tanggal', 'date', required: true, unique: true),
                    self::field('name', 'Nama Hari Libur', 'text', required: true),
                ],
            ),

            'reasons' => self::module(
                title: 'Alasan Absen / Cuti',
                menu: 'master',
                model: Reason::class,
                columns: [
                    ['data' => 'type_text', 'title' => 'Tipe'],
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'name', 'title' => 'Nama'],
                ],
                searchable: ['code', 'name'],
                fields: [
                    ['name' => 'type', 'label' => 'Tipe', 'type' => 'select', 'required' => true,
                        'options' => self::enumOptions(ReasonType::class)],
                    self::field('code', 'Kode', 'text', max: 7, required: true, upcase: true,
                        unique: ['absent_reasons', ['type', 'code']]),
                    self::field('name', 'Nama', 'text', required: true, upcase: true),
                ],
            ),

            'contracts' => self::module(
                title: 'Kontrak',
                menu: 'master',
                model: Contract::class,
                columns: [
                    ['data' => 'type_text', 'title' => 'Tipe'],
                    ['data' => 'letter_number', 'title' => 'No. Surat'],
                    ['data' => 'subject', 'title' => 'Perihal'],
                    ['data' => 'used', 'title' => 'Terpakai', 'render' => 'bool'],
                ],
                searchable: ['letter_number', 'subject'],
                fields: [
                    ['name' => 'type', 'label' => 'Tipe', 'type' => 'select', 'required' => true,
                        'options' => self::enumOptions(ContractType::class)],
                    self::field('letter_number', 'No. Surat', 'text', max: 27, required: true),
                    self::field('subject', 'Perihal', 'text', required: true, upcase: true),
                    self::field('description', 'Deskripsi', 'textarea'),
                    self::field('start_date', 'Tanggal Mulai', 'date', required: true),
                    self::field('end_date', 'Tanggal Selesai', 'date'),
                    self::field('signed_date', 'Tanggal Ditandatangani', 'date', required: true),
                    ['name' => 'tags', 'label' => 'Tags (pisahkan koma)', 'type' => 'taglist'],
                ],
            ),

            'companies' => self::module(
                title: 'Perusahaan',
                menu: 'company',
                model: Company::class,
                columns: [
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'name', 'title' => 'Nama'],
                    ['data' => 'email', 'title' => 'Email'],
                ],
                searchable: ['code', 'name', 'email'],
                order: ['name', 'asc'],
                fields: [
                    self::field('code', 'Kode', 'text', max: 7, required: true, upcase: true, unique: true),
                    self::field('name', 'Nama', 'text', required: true),
                    self::field('birth_day', 'Hari Lahir', 'date', required: true),
                    self::field('email', 'Email', 'email', required: true),
                    self::field('tax_number', 'NPWP', 'text', required: true),
                    ['name' => 'parent_id', 'label' => 'Perusahaan Induk', 'type' => 'select',
                        'model' => Company::class, 'text' => 'name', 'nullable' => true],
                ],
            ),

            'company-addresses' => self::module(
                title: 'Alamat Perusahaan',
                menu: 'company',
                model: CompanyAddress::class,
                columns: [
                    ['data' => 'company_name', 'title' => 'Perusahaan'],
                    ['data' => 'address', 'title' => 'Alamat'],
                    ['data' => 'city_name', 'title' => 'Kota'],
                ],
                searchable: ['address'],
                fields: [
                    ['name' => 'company_id', 'label' => 'Perusahaan', 'type' => 'select',
                        'model' => Company::class, 'text' => 'name', 'required' => true],
                    self::field('address', 'Alamat', 'textarea', required: true),
                    ['name' => 'region_id', 'label' => 'Propinsi', 'type' => 'select',
                        'model' => Region::class, 'text' => 'display'],
                    ['name' => 'city_id', 'label' => 'Kota', 'type' => 'select',
                        'model' => City::class, 'text' => 'display', 'dependsOn' => 'region_id'],
                    self::field('postal_code', 'Kode Pos', 'text', max: 5, required: true),
                    self::field('phone_number', 'Telepon', 'text', max: 17, required: true),
                    self::field('fax_number', 'Fax', 'text', max: 11, required: true),
                    ['name' => 'default_address', 'label' => 'Alamat Utama', 'type' => 'checkbox', 'default' => 1],
                ],
            ),

            'departments' => self::module(
                title: 'Departemen',
                menu: 'company',
                model: Department::class,
                columns: [
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'parent_name', 'title' => 'Induk'],
                    ['data' => 'name', 'title' => 'Nama'],
                ],
                searchable: ['code', 'name'],
                fields: [
                    self::field('code', 'Kode', 'text', max: 7, required: true, upcase: true, unique: true),
                    self::field('name', 'Nama', 'text', required: true),
                    ['name' => 'parent_id', 'label' => 'Induk', 'type' => 'select',
                        'model' => Department::class, 'text' => 'name', 'nullable' => true],
                ],
            ),

            'company-departments' => self::module(
                title: 'Departemen Perusahaan',
                menu: 'company',
                model: CompanyDepartment::class,
                columns: [
                    ['data' => 'company_name', 'title' => 'Perusahaan'],
                    ['data' => 'department_name', 'title' => 'Departemen'],
                ],
                searchable: ['department_id'],
                fields: [
                    ['name' => 'company_id', 'label' => 'Perusahaan', 'type' => 'select',
                        'model' => Company::class, 'text' => 'name', 'required' => true],
                    ['name' => 'department_id', 'label' => 'Departemen', 'type' => 'select',
                        'model' => Department::class, 'text' => 'name', 'required' => true,
                        'dependsOn' => 'company_id'],
                ],
            ),

            'job-levels' => self::module(
                title: 'Level Jabatan',
                menu: 'company',
                model: JobLevel::class,
                columns: [
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'name', 'title' => 'Nama'],
                    ['data' => 'parent_name', 'title' => 'Level Atas'],
                ],
                searchable: ['code', 'name'],
                fields: [
                    self::field('code', 'Kode', 'text', max: 7, required: true, upcase: true, unique: true),
                    self::field('name', 'Nama', 'text', required: true),
                    ['name' => 'parent_id', 'label' => 'Level Atas', 'type' => 'select',
                        'model' => JobLevel::class, 'text' => 'name', 'nullable' => true],
                ],
            ),

            'job-titles' => self::module(
                title: 'Jabatan',
                menu: 'company',
                model: JobTitle::class,
                columns: [
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'job_level_name', 'title' => 'Level'],
                    ['data' => 'name', 'title' => 'Nama'],
                ],
                searchable: ['code', 'name'],
                order: ['name', 'asc'],
                fields: [
                    ['name' => 'job_level_id', 'label' => 'Level Jabatan', 'type' => 'select',
                        'model' => JobLevel::class, 'text' => 'name', 'required' => true],
                    self::field('code', 'Kode', 'text', max: 9, required: true, upcase: true, unique: true),
                    self::field('name', 'Nama', 'text', required: true),
                ],
            ),
        ];
    }

    public static function get(string $key): ?array
    {
        $module = self::all()[$key] ?? null;

        return $module ? ['key' => $key] + $module : null;
    }

    public static function byMenu(string $menu): array
    {
        $result = [];

        foreach (self::all() as $key => $module) {
            if ($module['menu'] === $menu) {
                $result[$key] = ['key' => $key] + $module;
            }
        }

        return $result;
    }

    public static function menuRoles(): array
    {
        return ['master' => 'master_menu', 'company' => 'company_menu'];
    }

    private static function module(
        string $title,
        string $menu,
        string $model,
        array $fields,
        array $columns = [],
        array $searchable = [],
        array $order = ['updated_at', 'desc'],
    ): array {
        return [
            'title' => $title,
            'menu' => $menu,
            'model' => $model,
            'columns' => $columns,
            'searchable' => $searchable,
            'order' => $order,
            'fields' => $fields,
            'trash' => true,
        ];
    }

    private static function field(
        string $name,
        string $label,
        string $type,
        ?int $max = null,
        bool $required = false,
        bool $upcase = false,
        array|bool $unique = false,
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'max' => $max,
            'required' => $required,
            'upcase' => $upcase,
            'unique' => $unique,
        ];
    }

    private static function enumOptions(string $enum): array
    {
        return array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->label()],
            $enum::cases(),
        );
    }
}