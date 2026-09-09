<?php

namespace App\Support;

use App\Domain\Attendance\AttendanceCalculator;
use App\Domain\Attendance\ValidateAttendance;
use App\Enums\ContractType;
use App\Enums\FamilyRelation;
use App\Enums\Gender;
use App\Enums\IdentityType;
use App\Enums\MaritalStatus;
use App\Enums\MutationType;
use App\Enums\ReasonType;
use App\Enums\RiskRatio;
use App\Enums\SalaryState;
use App\Enums\SkillLevel;
use App\Enums\TaxGroup;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Attendance\Leave;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Shiftment;
use App\Models\Company\Company;
use App\Models\Company\CompanyAddress;
use App\Models\Company\CompanyDepartment;
use App\Models\Company\Department;
use App\Models\Company\JobLevel;
use App\Models\Company\JobTitle;
use App\Models\Employee\CareerHistory;
use App\Models\Employee\Employee;
use App\Models\Employee\EmployeeAddress;
use App\Models\Employee\EmployeeEducation;
use App\Models\Employee\EmployeeFamily;
use App\Models\Employee\EmployeeSkill;
use App\Models\Employee\Mutation;
use App\Models\Employee\Placement;
use App\Models\Master\City;
use App\Models\Master\Contract;
use App\Models\Master\EducationalInstitute;
use App\Models\Master\EducationTitle;
use App\Models\Master\Holiday;
use App\Models\Master\Reason;
use App\Models\Master\Region;
use App\Models\Master\Skill;
use App\Models\Master\SkillGroup;
use App\Models\Payroll\CompanyCost;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollDetail;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\SalaryAllowance;
use App\Models\Payroll\SalaryBenefit;
use App\Models\Payroll\SalaryBenefitHistory;
use App\Models\Payroll\SalaryComponent;
use App\Models\Tax\Tax;
use App\Models\Tax\TaxGroupHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
                menu: 'address',
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

            'employees' => self::module(
                title: 'Karyawan',
                menu: 'employee',
                model: Employee::class,
                columns: [
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'full_name', 'title' => 'Nama'],
                    ['data' => 'company_name', 'title' => 'Perusahaan'],
                    ['data' => 'department_name', 'title' => 'Departemen'],
                    ['data' => 'job_title_name', 'title' => 'Jabatan'],
                    ['data' => 'employee_status_text', 'title' => 'Status'],
                ],
                searchable: ['code', 'full_name', 'username', 'email', 'identity_number'],
                order: ['code', 'asc'],
                fields: [
                    ['name' => 'profile_image', 'label' => 'Foto', 'type' => 'image', 'collection' => 'profile'],
                    self::field('code', 'Kode', 'text', max: 17, upcase: true, unique: true),
                    self::field('full_name', 'Nama Lengkap', 'text', required: true, upcase: true),
                    ['name' => 'gender', 'label' => 'Jenis Kelamin', 'type' => 'select', 'required' => true,
                        'options' => self::enumOptions(Gender::class)],
                    ['name' => 'employee_status', 'label' => 'Status Karyawan', 'type' => 'select',
                        'options' => self::enumOptions(ContractType::class)],
                    self::field('join_date', 'Tanggal Bergabung', 'date', required: true),
                    self::field('resign_date', 'Tanggal Keluar', 'date'),
                    ['name' => 'contract_id', 'label' => 'Kontrak', 'type' => 'select',
                        'model' => Contract::class, 'text' => 'display', 'nullable' => true],
                    self::field('date_of_birth', 'Tanggal Lahir', 'date', required: true),
                    ['name' => 'region_of_birth_id', 'label' => 'Propinsi Lahir', 'type' => 'select',
                        'model' => Region::class, 'text' => 'display', 'nullable' => true],
                    ['name' => 'city_of_birth_id', 'label' => 'Kota Lahir', 'type' => 'select',
                        'model' => City::class, 'text' => 'display', 'nullable' => true,
                        'dependsOn' => 'region_of_birth_id'],
                    ['name' => 'identity_type', 'label' => 'Jenis Identitas', 'type' => 'select',
                        'options' => self::enumOptions(IdentityType::class)],
                    self::field('identity_number', 'Nomor Identitas', 'text', max: 27, required: true, unique: true),
                    ['name' => 'marital_status', 'label' => 'Status Perkawinan', 'type' => 'select',
                        'options' => self::enumOptions(MaritalStatus::class)],
                    self::field('email', 'Email', 'email', required: true, unique: true),
                    ['name' => 'company_id', 'label' => 'Perusahaan', 'type' => 'select',
                        'model' => Company::class, 'text' => 'name', 'nullable' => true],
                    ['name' => 'department_id', 'label' => 'Departemen', 'type' => 'select',
                        'model' => Department::class, 'text' => 'name', 'nullable' => true,
                        'dependsOn' => 'company_id'],
                    ['name' => 'job_level_id', 'label' => 'Level Jabatan', 'type' => 'select',
                        'model' => JobLevel::class, 'text' => 'name', 'nullable' => true],
                    ['name' => 'job_title_id', 'label' => 'Jabatan', 'type' => 'select',
                        'model' => JobTitle::class, 'text' => 'name', 'nullable' => true,
                        'dependsOn' => 'job_level_id'],
                    ['name' => 'supervisor_id', 'label' => 'Atasan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'nullable' => true],
                    self::field('username', 'Username', 'text', max: 64, unique: true),
                    ['name' => 'tax_group', 'label' => 'Kelompok Pajak', 'type' => 'select',
                        'options' => self::enumOptions(TaxGroup::class)],
                    ['name' => 'risk_ratio', 'label' => 'Rasio Risiko', 'type' => 'select',
                        'options' => self::enumOptions(RiskRatio::class)],
                    ['name' => 'have_overtime_benefit', 'label' => 'Berhak Lembur', 'type' => 'checkbox'],
                ],
            ),

            'employee-addresses' => self::module(
                title: 'Alamat Karyawan',
                menu: 'address',
                model: EmployeeAddress::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'address', 'title' => 'Alamat'],
                    ['data' => 'city_name', 'title' => 'Kota'],
                    ['data' => 'default_address', 'title' => 'Utama', 'render' => 'bool'],
                ],
                searchable: ['address'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    self::field('address', 'Alamat', 'textarea', required: true),
                    ['name' => 'region_id', 'label' => 'Propinsi', 'type' => 'select',
                        'model' => Region::class, 'text' => 'display', 'nullable' => true],
                    ['name' => 'city_id', 'label' => 'Kota', 'type' => 'select',
                        'model' => City::class, 'text' => 'display', 'nullable' => true,
                        'dependsOn' => 'region_id'],
                    self::field('postal_code', 'Kode Pos', 'text', max: 5, required: true),
                    self::field('phone_number', 'Telepon', 'text', max: 17, required: true),
                    self::field('fax_number', 'Fax', 'text', max: 11),
                    ['name' => 'default_address', 'label' => 'Alamat Utama', 'type' => 'checkbox', 'default' => 1],
                ],
            ),

            'placements' => self::module(
                title: 'Penempatan',
                menu: 'employee',
                model: Placement::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'company_name', 'title' => 'Perusahaan'],
                    ['data' => 'department_name', 'title' => 'Departemen'],
                    ['data' => 'job_title_name', 'title' => 'Jabatan'],
                    ['data' => 'active', 'title' => 'Aktif', 'render' => 'bool'],
                ],
                searchable: ['active'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    ['name' => 'company_id', 'label' => 'Perusahaan', 'type' => 'select',
                        'model' => Company::class, 'text' => 'name', 'nullable' => true],
                    ['name' => 'department_id', 'label' => 'Departemen', 'type' => 'select',
                        'model' => Department::class, 'text' => 'name', 'nullable' => true,
                        'dependsOn' => 'company_id'],
                    ['name' => 'job_level_id', 'label' => 'Level Jabatan', 'type' => 'select',
                        'model' => JobLevel::class, 'text' => 'name', 'nullable' => true],
                    ['name' => 'job_title_id', 'label' => 'Jabatan', 'type' => 'select',
                        'model' => JobTitle::class, 'text' => 'name', 'nullable' => true,
                        'dependsOn' => 'job_level_id'],
                    ['name' => 'supervisor_id', 'label' => 'Atasan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'nullable' => true],
                    ['name' => 'contract_id', 'label' => 'Kontrak', 'type' => 'select',
                        'model' => Contract::class, 'text' => 'display', 'nullable' => true],
                    ['name' => 'active', 'label' => 'Aktif', 'type' => 'checkbox', 'default' => 1],
                ],
            ),

            'mutations' => self::module(
                title: 'Mutasi',
                menu: 'employee',
                model: Mutation::class,
                columns: [
                    ['data' => 'type_text', 'title' => 'Jenis'],
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'old_job_title_name', 'title' => 'Jabatan Lama'],
                    ['data' => 'new_job_title_name', 'title' => 'Jabatan Baru'],
                    ['data' => 'new_department_name', 'title' => 'Dept. Baru'],
                ],
                searchable: ['type'],
                fields: [
                    ['name' => 'type', 'label' => 'Jenis Mutasi', 'type' => 'select', 'required' => true,
                        'options' => self::enumOptions(MutationType::class)],
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    ['name' => 'new_company_id', 'label' => 'Perusahaan Baru', 'type' => 'select',
                        'model' => Company::class, 'text' => 'name', 'nullable' => true],
                    ['name' => 'new_department_id', 'label' => 'Departemen Baru', 'type' => 'select',
                        'model' => Department::class, 'text' => 'name', 'nullable' => true,
                        'dependsOn' => 'new_company_id'],
                    ['name' => 'new_job_level_id', 'label' => 'Level Jabatan Baru', 'type' => 'select',
                        'model' => JobLevel::class, 'text' => 'name', 'nullable' => true],
                    ['name' => 'new_job_title_id', 'label' => 'Jabatan Baru', 'type' => 'select',
                        'model' => JobTitle::class, 'text' => 'name', 'nullable' => true,
                        'dependsOn' => 'new_job_level_id'],
                    ['name' => 'new_supervisor_id', 'label' => 'Atasan Baru', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'nullable' => true],
                    ['name' => 'contract_id', 'label' => 'Kontrak', 'type' => 'select',
                        'model' => Contract::class, 'text' => 'display', 'nullable' => true],
                ],
            ),

            'career-histories' => self::module(
                title: 'Riwayat Karir',
                menu: 'employee',
                model: CareerHistory::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'company_name', 'title' => 'Perusahaan'],
                    ['data' => 'department_name', 'title' => 'Departemen'],
                    ['data' => 'job_title_name', 'title' => 'Jabatan'],
                    ['data' => 'description', 'title' => 'Keterangan'],
                ],
                searchable: ['description'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    ['name' => 'company_id', 'label' => 'Perusahaan', 'type' => 'select',
                        'model' => Company::class, 'text' => 'name', 'nullable' => true],
                    ['name' => 'department_id', 'label' => 'Departemen', 'type' => 'select',
                        'model' => Department::class, 'text' => 'name', 'nullable' => true,
                        'dependsOn' => 'company_id'],
                    ['name' => 'job_level_id', 'label' => 'Level Jabatan', 'type' => 'select',
                        'model' => JobLevel::class, 'text' => 'name', 'nullable' => true],
                    ['name' => 'job_title_id', 'label' => 'Jabatan', 'type' => 'select',
                        'model' => JobTitle::class, 'text' => 'name', 'nullable' => true,
                        'dependsOn' => 'job_level_id'],
                    ['name' => 'supervisor_id', 'label' => 'Atasan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'nullable' => true],
                    ['name' => 'contract_id', 'label' => 'Kontrak', 'type' => 'select',
                        'model' => Contract::class, 'text' => 'display', 'nullable' => true],
                    self::field('description', 'Keterangan', 'text', max: 11, required: true, upcase: true),
                ],
            ),

            'employee-families' => self::module(
                title: 'Data Keluarga',
                menu: 'employee',
                model: EmployeeFamily::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'relation_text', 'title' => 'Hubungan'],
                    ['data' => 'name', 'title' => 'Nama'],
                    ['data' => 'gender_text', 'title' => 'JK'],
                    ['data' => 'date_of_birth', 'title' => 'Tanggal Lahir'],
                    ['data' => 'job', 'title' => 'Pekerjaan'],
                ],
                searchable: ['name', 'identity_number'],
                order: ['name', 'asc'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    ['name' => 'relation', 'label' => 'Hubungan', 'type' => 'select', 'required' => true,
                        'options' => self::enumOptions(FamilyRelation::class)],
                    self::field('name', 'Nama', 'text', required: true, upcase: true),
                    ['name' => 'gender', 'label' => 'Jenis Kelamin', 'type' => 'select',
                        'options' => self::enumOptions(Gender::class)],
                    self::field('date_of_birth', 'Tanggal Lahir', 'date'),
                    self::field('identity_number', 'Nomor Identitas', 'text', max: 27),
                    self::field('job', 'Pekerjaan', 'text'),
                ],
            ),

            'employee-educations' => self::module(
                title: 'Pendidikan',
                menu: 'employee',
                model: EmployeeEducation::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'education_title_name', 'title' => 'Jenjang'],
                    ['data' => 'education_institute_name', 'title' => 'Institusi'],
                    ['data' => 'year', 'title' => 'Tahun'],
                ],
                searchable: ['year'],
                order: ['year', 'desc'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    ['name' => 'education_title_id', 'label' => 'Jenjang', 'type' => 'select',
                        'model' => EducationTitle::class, 'text' => 'name', 'nullable' => true],
                    ['name' => 'education_institute_id', 'label' => 'Institusi', 'type' => 'select',
                        'model' => EducationalInstitute::class, 'text' => 'name', 'nullable' => true],
                    self::field('year', 'Tahun', 'text', max: 4),
                ],
            ),

            'employee-skills' => self::module(
                title: 'Keahlian',
                menu: 'employee',
                model: EmployeeSkill::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'skill_name', 'title' => 'Keahlian'],
                    ['data' => 'level_text', 'title' => 'Level'],
                ],
                searchable: [],
                order: ['updated_at', 'desc'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    ['name' => 'skill_id', 'label' => 'Keahlian', 'type' => 'select',
                        'model' => Skill::class, 'text' => 'name', 'required' => true],
                    ['name' => 'level', 'label' => 'Level', 'type' => 'select',
                        'options' => self::enumOptions(SkillLevel::class)],
                ],
            ),

            'attendances' => self::module(
                title: 'Absensi',
                menu: 'attendance',
                model: Attendance::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'attendance_date', 'title' => 'Tanggal'],
                    ['data' => 'shiftment_name', 'title' => 'Shift'],
                    ['data' => 'check_in', 'title' => 'Masuk'],
                    ['data' => 'check_out', 'title' => 'Keluar'],
                    ['data' => 'absent', 'title' => 'Absen', 'render' => 'bool'],
                ],
                searchable: ['attendance_date'],
                order: ['attendance_date', 'desc'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    self::field('attendance_date', 'Tanggal', 'date', required: true,
                        unique: ['attendances', ['employee_id', 'attendance_date']]),
                    ['name' => 'shiftment_id', 'label' => 'Shift', 'type' => 'select',
                        'model' => Shiftment::class, 'text' => 'name', 'nullable' => true],
                    self::field('check_in', 'Jam Masuk', 'text'),
                    self::field('check_out', 'Jam Keluar', 'text'),
                    ['name' => 'absent', 'label' => 'Absen', 'type' => 'checkbox'],
                    ['name' => 'reason_id', 'label' => 'Alasan', 'type' => 'select',
                        'model' => Reason::class, 'text' => 'name', 'nullable' => true],
                    self::field('description', 'Keterangan', 'textarea'),
                ],
            ) + ['validator' => static function (Request $request, array $data): void {
                $attendance = new Attendance;
                $attendance->fill($data);
                $attendance->absent = $request->boolean('absent');

                app(AttendanceCalculator::class)->calculate($attendance);

                if (! ValidateAttendance::validate($attendance)) {
                    if ($attendance->absent) {
                        throw ValidationException::withMessages([
                            'reason_id' => 'Alasan wajib diisi ketika karyawan tidak masuk (absen).',
                        ]);
                    }

                    throw ValidationException::withMessages([
                        'check_in' => 'Jam masuk wajib diisi ketika karyawan hadir.',
                        'check_out' => 'Jam keluar wajib diisi ketika karyawan hadir.',
                    ]);
                }
            }],

            'attendance-summaries' => self::module(
                title: 'Rekap Absensi',
                menu: 'attendance',
                model: AttendanceSummary::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'year', 'title' => 'Tahun'],
                    ['data' => 'month', 'title' => 'Bulan'],
                    ['data' => 'total_workday', 'title' => 'Hari Kerja'],
                    ['data' => 'total_in', 'title' => 'Hadir'],
                    ['data' => 'total_absent', 'title' => 'Absen'],
                    ['data' => 'total_loyality', 'title' => 'Loyalitas'],
                    ['data' => 'total_overtime', 'title' => 'Lembur'],
                ],
                searchable: ['year', 'month'],
                order: ['year', 'desc'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    self::field('year', 'Tahun', 'text', max: 4, required: true),
                    self::field('month', 'Bulan', 'text', max: 2, required: true),
                    self::field('total_workday', 'Hari Kerja', 'text', max: 2),
                    self::field('total_in', 'Hadir', 'text', max: 2),
                    self::field('total_loyality', 'Loyalitas', 'text', max: 4),
                    self::field('total_absent', 'Absen', 'text', max: 2),
                    self::field('total_overtime', 'Lembur', 'text', max: 4),
                ],
            ),

            'overtimes' => self::module(
                title: 'Lembur',
                menu: 'overtime',
                model: Overtime::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'overtime_date', 'title' => 'Tanggal'],
                    ['data' => 'start_hour', 'title' => 'Mulai'],
                    ['data' => 'end_hour', 'title' => 'Selesai'],
                    ['data' => 'raw_value', 'title' => 'Jam'],
                    ['data' => 'calculated_value', 'title' => 'Nilai'],
                    ['data' => 'holiday', 'title' => 'Libur', 'render' => 'bool'],
                ],
                searchable: ['overtime_date'],
                order: ['overtime_date', 'desc'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    self::field('overtime_date', 'Tanggal', 'date', required: true,
                        unique: ['overtimes', ['employee_id', 'overtime_date']]),
                    ['name' => 'shiftment_id', 'label' => 'Shift', 'type' => 'select',
                        'model' => Shiftment::class, 'text' => 'name', 'nullable' => true],
                    self::field('start_hour', 'Mulai', 'text', required: true),
                    self::field('end_hour', 'Selesai', 'text', required: true),
                    self::field('description', 'Keterangan', 'textarea'),
                    self::field('raw_value', 'Jam', 'text'),
                    self::field('calculated_value', 'Nilai', 'text'),
                    ['name' => 'holiday', 'label' => 'Hari Libur', 'type' => 'checkbox'],
                    ['name' => 'overday', 'label' => 'Lintas Hari', 'type' => 'checkbox'],
                    ['name' => 'approved_by_id', 'label' => 'Disetujui Oleh', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'nullable' => true],
                ],
            ),

            'leaves' => self::module(
                title: 'Cuti',
                menu: 'leave',
                model: Leave::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'leave_date', 'title' => 'Tanggal'],
                    ['data' => 'reason_name', 'title' => 'Alasan'],
                    ['data' => 'amount', 'title' => 'Jumlah Hari'],
                    ['data' => 'description', 'title' => 'Keterangan'],
                ],
                searchable: ['leave_date'],
                order: ['leave_date', 'desc'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    self::field('leave_date', 'Tanggal', 'date', required: true,
                        unique: ['leaves', ['employee_id', 'leave_date']]),
                    ['name' => 'reason_id', 'label' => 'Alasan', 'type' => 'select',
                        'model' => Reason::class, 'text' => 'name', 'required' => true],
                    self::field('amount', 'Jumlah Hari', 'text', max: 2, required: true),
                    self::field('description', 'Keterangan', 'textarea'),
                ],
            ),

            'salary-components' => self::module(
                title: 'Komponen Gaji',
                menu: 'payroll',
                model: SalaryComponent::class,
                columns: [
                    ['data' => 'code', 'title' => 'Kode'],
                    ['data' => 'name', 'title' => 'Nama'],
                    ['data' => 'state_text', 'title' => 'Tipe'],
                    ['data' => 'fixed', 'title' => 'Tetap', 'render' => 'bool'],
                ],
                searchable: ['code', 'name'],
                order: ['code', 'asc'],
                fields: [
                    self::field('code', 'Kode', 'text', max: 7, required: true, upcase: true, unique: true),
                    self::field('name', 'Nama', 'text', required: true, upcase: true),
                    ['name' => 'state', 'label' => 'Tipe', 'type' => 'select', 'required' => true,
                        'options' => self::enumOptions(SalaryState::class)],
                    ['name' => 'fixed', 'label' => 'Tunjangan Tetap', 'type' => 'checkbox', 'default' => 0],
                ],
            ),

            'salary-benefits' => self::module(
                title: 'Gaji Pokok & Tunjangan',
                menu: 'payroll',
                model: SalaryBenefit::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'component_name', 'title' => 'Komponen'],
                    ['data' => 'benefit_value', 'title' => 'Nilai', 'render' => 'money'],
                ],
                searchable: [],
                order: ['updated_at', 'desc'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    ['name' => 'component_id', 'label' => 'Komponen', 'type' => 'select',
                        'model' => SalaryComponent::class, 'text' => 'name', 'required' => true],
                    self::field('benefit_value', 'Nilai', 'text', required: true),
                ],
            ),

            'salary-allowances' => self::module(
                title: 'Tunjangan & Potongan',
                menu: 'payroll',
                model: SalaryAllowance::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'component_name', 'title' => 'Komponen'],
                    ['data' => 'year', 'title' => 'Tahun'],
                    ['data' => 'month', 'title' => 'Bulan'],
                    ['data' => 'benefit_value', 'title' => 'Nilai', 'render' => 'money'],
                ],
                searchable: [],
                order: ['updated_at', 'desc'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    ['name' => 'component_id', 'label' => 'Komponen', 'type' => 'select',
                        'model' => SalaryComponent::class, 'text' => 'name', 'required' => true],
                    self::field('year', 'Tahun', 'text', max: 4, required: true),
                    self::field('month', 'Bulan', 'text', max: 2, required: true),
                    self::field('benefit_value', 'Nilai', 'text', required: true),
                ],
            ),

            'salary-benefit-histories' => self::module(
                title: 'Riwayat Perubahan Gaji',
                menu: 'payroll',
                model: SalaryBenefitHistory::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'component_name', 'title' => 'Komponen'],
                    ['data' => 'old_benefit_value', 'title' => 'Nilai Lama', 'render' => 'money'],
                    ['data' => 'new_benefit_value', 'title' => 'Nilai Baru', 'render' => 'money'],
                    ['data' => 'description', 'title' => 'Keterangan'],
                ],
                searchable: [],
                order: ['updated_at', 'desc'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    ['name' => 'component_id', 'label' => 'Komponen', 'type' => 'select',
                        'model' => SalaryComponent::class, 'text' => 'name', 'required' => true],
                    ['name' => 'contract_id', 'label' => 'Kontrak', 'type' => 'select',
                        'model' => Contract::class, 'text' => 'display', 'nullable' => true],
                    self::field('new_benefit_value', 'Nilai Baru', 'text', required: true),
                    self::field('description', 'Keterangan', 'textarea'),
                ],
            ),

            'payroll-periods' => self::module(
                title: 'Periode Penggajian',
                menu: 'payroll',
                model: PayrollPeriod::class,
                columns: [
                    ['data' => 'company_name', 'title' => 'Perusahaan'],
                    ['data' => 'year', 'title' => 'Tahun'],
                    ['data' => 'month', 'title' => 'Bulan'],
                    ['data' => 'closed', 'title' => 'Ditutup', 'render' => 'bool'],
                ],
                searchable: [],
                order: ['year', 'desc'],
                fields: [
                    ['name' => 'company_id', 'label' => 'Perusahaan', 'type' => 'select',
                        'model' => Company::class, 'text' => 'name', 'required' => true],
                    self::field('year', 'Tahun', 'text', max: 4, required: true),
                    self::field('month', 'Bulan', 'text', max: 2, required: true),
                    ['name' => 'closed', 'label' => 'Ditutup', 'type' => 'checkbox', 'default' => 1],
                ],
            ),

            'payrolls' => self::module(
                title: 'Riwayat Penggajian',
                menu: 'payroll',
                model: Payroll::class,
                columns: [
                    ['data' => 'period_label', 'title' => 'Periode'],
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'take_home_pay', 'title' => 'Total Gaji', 'render' => 'money'],
                ],
                searchable: [],
                order: ['updated_at', 'desc'],
                fields: [
                    ['name' => 'period_id', 'label' => 'Periode', 'type' => 'select',
                        'model' => PayrollPeriod::class, 'text' => 'display', 'required' => true],
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    self::field('take_home_pay', 'Total Gaji', 'text', required: true),
                ],
            ),

            'payroll-details' => self::module(
                title: 'Rincian Gaji',
                menu: 'payroll',
                model: PayrollDetail::class,
                columns: [
                    ['data' => 'payroll_label', 'title' => 'Payroll'],
                    ['data' => 'component_name', 'title' => 'Komponen'],
                    ['data' => 'benefit_value', 'title' => 'Nilai', 'render' => 'money'],
                ],
                searchable: [],
                order: ['updated_at', 'desc'],
                fields: [
                    ['name' => 'payroll_id', 'label' => 'Payroll', 'type' => 'select',
                        'model' => Payroll::class, 'text' => 'display', 'required' => true],
                    ['name' => 'component_id', 'label' => 'Komponen', 'type' => 'select',
                        'model' => SalaryComponent::class, 'text' => 'name', 'required' => true],
                    self::field('benefit_value', 'Nilai', 'text', required: true),
                ],
            ),

            'company-costs' => self::module(
                title: 'Beban Perusahaan',
                menu: 'payroll',
                model: CompanyCost::class,
                columns: [
                    ['data' => 'payroll_label', 'title' => 'Payroll'],
                    ['data' => 'component_name', 'title' => 'Komponen'],
                    ['data' => 'benefit_value', 'title' => 'Nilai', 'render' => 'money'],
                ],
                searchable: [],
                order: ['updated_at', 'desc'],
                fields: [
                    ['name' => 'payroll_id', 'label' => 'Payroll', 'type' => 'select',
                        'model' => Payroll::class, 'text' => 'display', 'required' => true],
                    ['name' => 'component_id', 'label' => 'Komponen', 'type' => 'select',
                        'model' => SalaryComponent::class, 'text' => 'name', 'required' => true],
                    self::field('benefit_value', 'Nilai', 'text', required: true),
                ],
            ),

            'taxes' => self::module(
                title: 'Riwayat Pajak',
                menu: 'payroll',
                model: Tax::class,
                columns: [
                    ['data' => 'period_label', 'title' => 'Periode'],
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'tax_value', 'title' => 'Pajak', 'render' => 'money'],
                    ['data' => 'untaxable', 'title' => 'PTKP', 'render' => 'money'],
                    ['data' => 'taxable', 'title' => 'PKP', 'render' => 'money'],
                ],
                searchable: [],
                order: ['updated_at', 'desc'],
                fields: [
                    ['name' => 'period_id', 'label' => 'Periode', 'type' => 'select',
                        'model' => PayrollPeriod::class, 'text' => 'display', 'required' => true],
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    ['name' => 'tax_group', 'label' => 'Kelompok Pajak', 'type' => 'select',
                        'options' => self::enumOptions(TaxGroup::class), 'required' => true],
                    self::field('untaxable', 'PTKP', 'text'),
                    self::field('taxable', 'PKP', 'text'),
                    self::field('tax_value', 'Pajak', 'text', required: true),
                ],
            ),

            'tax-group-histories' => self::module(
                title: 'Riwayat Kelompok Pajak',
                menu: 'payroll',
                model: TaxGroupHistory::class,
                columns: [
                    ['data' => 'employee_name', 'title' => 'Karyawan'],
                    ['data' => 'old_tax_group', 'title' => 'Pajak Lama', 'render' => fn ($row) => $row->old_tax_group?->label() ?? ''],
                    ['data' => 'new_tax_group', 'title' => 'Pajak Baru', 'render' => fn ($row) => $row->new_tax_group?->label() ?? ''],
                    ['data' => 'old_risk_ratio', 'title' => 'Risiko Lama', 'render' => fn ($row) => $row->old_risk_ratio?->label() ?? ''],
                    ['data' => 'new_risk_ratio', 'title' => 'Risiko Baru', 'render' => fn ($row) => $row->new_risk_ratio?->label() ?? ''],
                ],
                searchable: [],
                order: ['updated_at', 'desc'],
                fields: [
                    ['name' => 'employee_id', 'label' => 'Karyawan', 'type' => 'select',
                        'model' => Employee::class, 'text' => 'display', 'required' => true],
                    ['name' => 'old_tax_group', 'label' => 'Kelompok Pajak Lama', 'type' => 'select',
                        'options' => self::enumOptions(TaxGroup::class)],
                    ['name' => 'new_tax_group', 'label' => 'Kelompok Pajak Baru', 'type' => 'select',
                        'options' => self::enumOptions(TaxGroup::class)],
                    ['name' => 'old_risk_ratio', 'label' => 'Rasio Risiko Lama', 'type' => 'select',
                        'options' => self::enumOptions(RiskRatio::class)],
                    ['name' => 'new_risk_ratio', 'label' => 'Rasio Risiko Baru', 'type' => 'select',
                        'options' => self::enumOptions(RiskRatio::class)],
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
        return [
            'master' => 'master_menu',
            'company' => 'company_menu',
            'employee' => 'employee_menu',
            'address' => 'address_menu',
            'attendance' => 'attendance_menu',
            'overtime' => 'overtime_menu',
            'leave' => 'leave_menu',
            'payroll' => 'payroll_menu',
        ];
    }

    private static function module(
        string $title,
        string $menu,
        string $model,
        array $fields,
        array $columns = [],
        array $searchable = [],
        array $order = ['updated_at', 'desc'],
        ?callable $validate = null,
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
            'validate' => $validate,
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
