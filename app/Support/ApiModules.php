<?php

namespace App\Support;

use App\Http\Resources\Api\AttendanceResource;
use App\Http\Resources\Api\AttendanceSummaryResource;
use App\Http\Resources\Api\CityResource;
use App\Http\Resources\Api\CompanyCostResource;
use App\Http\Resources\Api\CompanyResource;
use App\Http\Resources\Api\ContractResource;
use App\Http\Resources\Api\DepartmentResource;
use App\Http\Resources\Api\EmployeeResource;
use App\Http\Resources\Api\HolidayResource;
use App\Http\Resources\Api\JobLevelResource;
use App\Http\Resources\Api\JobTitleResource;
use App\Http\Resources\Api\LeaveResource;
use App\Http\Resources\Api\OvertimeResource;
use App\Http\Resources\Api\PayrollPeriodResource;
use App\Http\Resources\Api\PayrollResource;
use App\Http\Resources\Api\ReasonResource;
use App\Http\Resources\Api\RegionResource;
use App\Http\Resources\Api\SalaryAllowanceResource;
use App\Http\Resources\Api\SalaryBenefitResource;
use App\Http\Resources\Api\SalaryComponentResource;
use App\Http\Resources\Api\ShiftmentResource;
use App\Http\Resources\Api\SkillGroupResource;
use App\Http\Resources\Api\SkillResource;
use App\Http\Resources\Api\TaxGroupHistoryResource;
use App\Http\Resources\Api\TaxResource;
use App\Http\Resources\Api\WorkshiftResource;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Attendance\Leave;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Shiftment;
use App\Models\Attendance\Workshift;
use App\Models\Company\Company;
use App\Models\Company\Department;
use App\Models\Company\JobLevel;
use App\Models\Company\JobTitle;
use App\Models\Employee\Employee;
use App\Models\Master\City;
use App\Models\Master\Contract;
use App\Models\Master\Holiday;
use App\Models\Master\Reason;
use App\Models\Master\Region;
use App\Models\Master\Skill;
use App\Models\Master\SkillGroup;
use App\Models\Payroll\CompanyCost;
use App\Models\Payroll\Payroll;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\SalaryAllowance;
use App\Models\Payroll\SalaryBenefit;
use App\Models\Payroll\SalaryComponent;
use App\Models\Tax\Tax;
use App\Models\Tax\TaxGroupHistory;

/**
 * Registri modul API: memetakan slug rute ke model, resource, ability,
 * kolom pencarian, dan (opsional) konteks perusahaan + aturan mutasi.
 * Meniru pola MasterModules untuk sisi web.
 */
final class ApiModules
{
    /**
     * @return array<string, array>
     */
    public static function all(): array
    {
        return [
            // ---- Master ----
            'regions' => self::module(
                title: 'Wilayah',
                model: Region::class,
                resource: RegionResource::class,
                ability: 'view_master',
                searchable: ['name'],
            ),

            'cities' => self::module(
                title: 'Kota',
                model: City::class,
                resource: CityResource::class,
                ability: 'view_master',
                searchable: ['code', 'name'],
                order: ['name', 'asc'],
                with: ['region'],
                mutable: true,
                rules: [
                    'region_id' => ['required', 'exists:regions,id'],
                    'code' => ['required', 'string', 'max:10'],
                    'name' => ['required', 'string', 'max:255'],
                ],
            ),

            'holidays' => self::module(
                title: 'Hari Libur',
                model: Holiday::class,
                resource: HolidayResource::class,
                ability: 'view_master',
                searchable: ['name'],
                order: ['holiday_date', 'desc'],
                mutable: true,
                rules: [
                    'holiday_date' => ['required', 'date'],
                    'name' => ['required', 'string', 'max:255'],
                ],
            ),

            'reasons' => self::module(
                title: 'Alasan',
                model: Reason::class,
                resource: ReasonResource::class,
                ability: 'view_reason',
                searchable: ['code', 'name'],
                mutable: true,
                write_ability: 'manage_master',
                rules: [
                    'type' => ['required', 'string'],
                    'code' => ['required', 'string', 'max:10'],
                    'name' => ['required', 'string', 'max:255'],
                ],
            ),

            'skill-groups' => self::module(
                title: 'Kelompok Keahlian',
                model: SkillGroup::class,
                resource: SkillGroupResource::class,
                ability: 'view_master',
                searchable: ['name'],
                mutable: true,
                rules: [
                    'name' => ['required', 'string', 'max:255'],
                    'parent_id' => ['nullable', 'exists:skill_groups,id'],
                ],
            ),

            'skills' => self::module(
                title: 'Keahlian',
                model: Skill::class,
                resource: SkillResource::class,
                ability: 'view_master',
                searchable: ['name'],
                with: ['skillGroup'],
                mutable: true,
                rules: [
                    'skill_group_id' => ['required', 'exists:skill_groups,id'],
                    'name' => ['required', 'string', 'max:255'],
                ],
            ),

            'contracts' => self::module(
                title: 'Kontrak',
                model: Contract::class,
                resource: ContractResource::class,
                ability: 'view_master',
                searchable: ['letter_number', 'subject'],
                order: ['created_at', 'desc'],
            ),

            // ---- Organisasi ----
            'companies' => self::module(
                title: 'Perusahaan',
                model: Company::class,
                resource: CompanyResource::class,
                ability: 'view_company',
                searchable: ['code', 'name'],
                with: ['parent'],
                mutable: true,
                rules: [
                    'code' => ['required', 'string', 'max:10'],
                    'name' => ['required', 'string', 'max:255'],
                    'parent_id' => ['nullable', 'exists:companies,id'],
                    'email' => ['nullable', 'email'],
                    'tax_number' => ['nullable', 'string'],
                ],
            ),

            'departments' => self::module(
                title: 'Departemen',
                model: Department::class,
                resource: DepartmentResource::class,
                ability: 'view_company',
                searchable: ['code', 'name'],
                order: ['name', 'asc'],
                with: ['parent'],
            ),

            'job-levels' => self::module(
                title: 'Jenjang Jabatan',
                model: JobLevel::class,
                resource: JobLevelResource::class,
                ability: 'view_company',
                searchable: ['code', 'name'],
                order: ['code', 'asc'],
                with: ['parent'],
                mutable: true,
                rules: [
                    'code' => ['required', 'string', 'max:10'],
                    'name' => ['required', 'string', 'max:255'],
                    'parent_id' => ['nullable', 'exists:job_levels,id'],
                ],
            ),

            'job-titles' => self::module(
                title: 'Jabatan',
                model: JobTitle::class,
                resource: JobTitleResource::class,
                ability: 'view_company',
                searchable: ['code', 'name'],
                with: ['jobLevel'],
                mutable: true,
                rules: [
                    'code' => ['required', 'string', 'max:10'],
                    'name' => ['required', 'string', 'max:255'],
                    'job_level_id' => ['required', 'exists:job_levels,id'],
                ],
            ),

            // ---- Karyawan ----
            'employees' => self::module(
                title: 'Karyawan',
                model: Employee::class,
                resource: EmployeeResource::class,
                ability: 'view_employee',
                searchable: ['code', 'full_name'],
                with: ['supervisor', 'company', 'department', 'jobLevel', 'jobTitle', 'contract'],
                scope: self::companyScope('company_id'),
            ),

            // ---- Kehadiran ----
            'shiftments' => self::module(
                title: 'Shift',
                model: Shiftment::class,
                resource: ShiftmentResource::class,
                ability: 'view_attendance',
                searchable: ['code', 'name'],
                mutable: true,
                rules: [
                    'code' => ['required', 'string', 'max:10'],
                    'name' => ['required', 'string', 'max:255'],
                    'start_hour' => ['nullable'],
                    'end_hour' => ['nullable'],
                ],
            ),

            'attendances' => self::module(
                title: 'Kehadiran',
                model: Attendance::class,
                resource: AttendanceResource::class,
                ability: 'view_my_attendance',
                order: ['attendance_date', 'desc'],
                with: ['employee', 'shiftment', 'reason'],
                scope: self::viaEmployeeCompanyScope(),
                mutable: true,
                rules: [
                    'attendance_date' => ['required', 'date'],
                    'check_in' => ['nullable'],
                    'check_out' => ['nullable'],
                    'absent' => ['nullable', 'boolean'],
                    'description' => ['nullable', 'string', 'max:255'],
                    'shiftment_id' => ['nullable', 'exists:shiftments,id'],
                    'reason_id' => ['nullable', 'exists:absent_reasons,id'],
                ],
                update_rules: [
                    'attendance_date' => ['nullable', 'date'],
                    'check_in' => ['nullable'],
                    'check_out' => ['nullable'],
                    'absent' => ['nullable', 'boolean'],
                    'description' => ['nullable', 'string', 'max:255'],
                    'shiftment_id' => ['nullable', 'exists:shiftments,id'],
                    'reason_id' => ['nullable', 'exists:absent_reasons,id'],
                ],
                self_service: true,
                self_unique: 'attendance_date',
                managed_by: 'view_attendance',
            ),

            'attendance-summaries' => self::module(
                title: 'Rekap Kehadiran',
                model: AttendanceSummary::class,
                resource: AttendanceSummaryResource::class,
                ability: 'view_attendance',
                order: ['year', 'desc'],
                with: ['employee'],
                scope: self::viaEmployeeCompanyScope(),
            ),

            'workshifts' => self::module(
                title: 'Shift Kerja',
                model: Workshift::class,
                resource: WorkshiftResource::class,
                ability: 'view_attendance',
                order: ['start_date', 'desc'],
                with: ['employee', 'shiftment'],
                scope: self::viaEmployeeCompanyScope(),
            ),

            'overtimes' => self::module(
                title: 'Lembur',
                model: Overtime::class,
                resource: OvertimeResource::class,
                ability: 'view_my_overtime',
                order: ['overtime_date', 'desc'],
                with: ['employee', 'shiftment', 'approvedBy'],
                scope: self::viaEmployeeCompanyScope(),
                mutable: true,
                self_service: true,
                self_unique: 'overtime_date',
                managed_by: 'view_overtime',
                rules: [
                    'overtime_date' => ['required', 'date'],
                    'start_hour' => ['required'],
                    'end_hour' => ['required', 'after:start_hour'],
                ],
                update_rules: [
                    'overtime_date' => ['nullable', 'date'],
                    'start_hour' => ['nullable'],
                    'end_hour' => ['nullable'],
                ],
            ),

            'leaves' => self::module(
                title: 'Cuti',
                model: Leave::class,
                resource: LeaveResource::class,
                ability: 'view_my_leave',
                order: ['leave_date', 'desc'],
                with: ['employee', 'reason', 'approvedBy'],
                scope: self::viaEmployeeCompanyScope(),
                mutable: true,
                self_service: true,
                self_unique: 'leave_date',
                managed_by: 'view_leave',
                rules: [
                    'leave_date' => ['required', 'date'],
                    'reason_id' => ['required', 'exists:absent_reasons,id'],
                    'amount' => ['required', 'integer', 'min:1', 'max:31'],
                    'description' => ['nullable', 'string', 'max:255'],
                ],
                update_rules: [
                    'leave_date' => ['nullable', 'date'],
                    'reason_id' => ['nullable', 'exists:absent_reasons,id'],
                    'amount' => ['nullable', 'integer', 'min:1', 'max:31'],
                    'description' => ['nullable', 'string', 'max:255'],
                ],
            ),

            // ---- Payroll ----
            'payroll-periods' => self::module(
                title: 'Periode Payroll',
                model: PayrollPeriod::class,
                resource: PayrollPeriodResource::class,
                ability: 'view_payroll',
                order: ['year', 'desc'],
                with: ['company'],
                scope: self::companyScope('company_id'),
            ),

            'payrolls' => self::module(
                title: 'Payroll',
                model: Payroll::class,
                resource: PayrollResource::class,
                ability: 'view_payroll',
                order: ['created_at', 'desc'],
                with: ['employee', 'period', 'details'],
                scope: self::viaEmployeeCompanyScope(),
            ),

            'salary-components' => self::module(
                title: 'Komponen Gaji',
                model: SalaryComponent::class,
                resource: SalaryComponentResource::class,
                ability: 'view_payroll',
                searchable: ['code', 'name'],
                mutable: true,
                rules: [
                    'code' => ['required', 'string', 'max:10'],
                    'name' => ['required', 'string', 'max:255'],
                    'state' => ['required', 'string'],
                ],
            ),

            'salary-benefits' => self::module(
                title: 'Tunjangan',
                model: SalaryBenefit::class,
                resource: SalaryBenefitResource::class,
                ability: 'view_payroll',
                with: ['employee', 'component'],
                scope: self::viaEmployeeCompanyScope(),
            ),

            'salary-allowances' => self::module(
                title: 'Tunjangan Perusahaan',
                model: SalaryAllowance::class,
                resource: SalaryAllowanceResource::class,
                ability: 'view_payroll',
                with: ['employee', 'component'],
                scope: self::viaEmployeeCompanyScope(),
            ),

            'company-costs' => self::module(
                title: 'Biaya Perusahaan',
                model: CompanyCost::class,
                resource: CompanyCostResource::class,
                ability: 'view_payroll',
                with: ['component'],
            ),

            // ---- Pajak ----
            'taxes' => self::module(
                title: 'Pajak',
                model: Tax::class,
                resource: TaxResource::class,
                ability: 'view_payroll',
                order: ['created_at', 'desc'],
                with: ['employee', 'period'],
                scope: self::viaEmployeeCompanyScope(),
            ),

            'tax-group-histories' => self::module(
                title: 'Riwayat Kelompok Pajak',
                model: TaxGroupHistory::class,
                resource: TaxGroupHistoryResource::class,
                ability: 'view_employee',
                order: ['created_at', 'desc'],
                with: ['employee'],
                scope: self::viaEmployeeCompanyScope(),
            ),
        ];
    }

    public static function get(string $key): ?array
    {
        $module = self::all()[$key] ?? null;

        return $module ? ['key' => $key] + $module : null;
    }

    /**
     * Scope konteks perusahaan: selain SUPER_ADMIN hanya melihat
     * data perusahaannya sendiri.
     */
    private static function companyScope(string $column): callable
    {
        return static function ($query, Employee $user) use ($column): void {
            if ($user->hasRole('SUPER_ADMIN')) {
                return;
            }

            $query->where($column, $user->company_id);
        };
    }

    /**
     * Scope konteks perusahaan lewat kolom employee_id:
     * data di-scope ke perusahaan pengguna yang sedang login.
     */
    private static function viaEmployeeCompanyScope(): callable
    {
        return static function ($query, Employee $user): void {
            if ($user->hasRole('SUPER_ADMIN')) {
                return;
            }

            $query->whereHas('employee', fn ($builder) => $builder->where('company_id', $user->company_id));
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function module(
        string $title,
        string $model,
        string $resource,
        string $ability,
        array $searchable = [],
        array $order = ['updated_at', 'desc'],
        array $with = [],
        bool $mutable = false,
        array $rules = [],
        array $update_rules = [],
        ?callable $scope = null,
        bool $self_service = false,
        ?string $self_unique = null,
        ?string $managed_by = null,
        ?string $write_ability = null,
    ): array {
        return [
            'title' => $title,
            'model' => $model,
            'resource' => $resource,
            'ability' => $ability,
            'searchable' => $searchable,
            'order' => $order,
            'with' => $with,
            'mutable' => $mutable,
            'rules' => $rules,
            'update_rules' => $update_rules,
            'scope' => $scope,
            'self_service' => $self_service,
            'self_unique' => $self_unique,
            'managed_by' => $managed_by,
            'write_ability' => $write_ability,
        ];
    }
}
