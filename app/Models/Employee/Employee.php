<?php

namespace App\Models\Employee;

use App\Enums\ContractType;
use App\Enums\Gender;
use App\Enums\IdentityType;
use App\Enums\MaritalStatus;
use App\Enums\RiskRatio;
use App\Enums\TaxGroup;
use App\Models\Company\Company;
use App\Models\Company\Department;
use App\Models\Company\JobLevel;
use App\Models\Company\JobTitle;
use App\Models\Master\City;
use App\Models\Master\Contract;
use App\Models\Master\Region;
use App\Support\Concerns\Blameable;
use App\Support\StringUtil;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Authenticatable
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;
    use HasRoles;
    use Notifiable;

    public const DEFAULT_ROLE = 'ROLE_EMPLOYEE';

    protected $table = 'employees';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'join_date',
        'employee_status',
        'contract_id',
        'company_id',
        'department_id',
        'job_level_id',
        'job_title_id',
        'supervisor_id',
        'code',
        'full_name',
        'gender',
        'region_of_birth_id',
        'city_of_birth_id',
        'date_of_birth',
        'identity_number',
        'identity_type',
        'marital_status',
        'email',
        'address_id',
        'leave_balance',
        'tax_group',
        'resign_date',
        'have_overtime_benefit',
        'risk_ratio',
        'username',
        'password',
        'profile_image',
        'profile_size',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'employee_status' => ContractType::class,
        'gender' => Gender::class,
        'identity_type' => IdentityType::class,
        'marital_status' => MaritalStatus::class,
        'tax_group' => TaxGroup::class,
        'risk_ratio' => RiskRatio::class,
        'join_date' => 'date',
        'date_of_birth' => 'date',
        'resign_date' => 'date',
        'have_overtime_benefit' => 'boolean',
        'password' => 'hashed',
    ];

    protected static function booted(): void
    {
        static::creating(function (Employee $employee) {
            if (empty($employee->employee_status)) {
                $employee->employee_status = ContractType::TEMPORARY;
            }
            if (empty($employee->gender)) {
                $employee->gender = Gender::MALE;
            }
            if (empty($employee->identity_type)) {
                $employee->identity_type = IdentityType::ID_CARD;
            }
            if (empty($employee->marital_status)) {
                $employee->marital_status = MaritalStatus::SINGLE;
            }
            if (empty($employee->leave_balance)) {
                $employee->leave_balance = 12;
            }
            if (empty($employee->tax_group)) {
                $employee->tax_group = TaxGroup::TK0;
            }
            if (empty($employee->risk_ratio)) {
                $employee->risk_ratio = RiskRatio::RISK_VERY_LOW;
            }
            if (empty($employee->have_overtime_benefit)) {
                $employee->have_overtime_benefit = false;
            }
        });
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supervisor_id');
    }

    public function subordinate(): HasMany
    {
        return $this->hasMany(self::class, 'supervisor_id');
    }

    public function regionOfBirth(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_of_birth_id');
    }

    public function cityOfBirth(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_of_birth_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(EmployeeAddress::class, 'address_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(EmployeeAddress::class);
    }

    public function getEmployeeStatusText(): string
    {
        return $this->employee_status?->label() ?? '';
    }

    public function getGenderText(): string
    {
        return $this->gender?->label() ?? '';
    }

    public function getIdentityTypeText(): string
    {
        return $this->identity_type?->label() ?? '';
    }

    public function getMaritalStatusText(): string
    {
        return $this->marital_status?->label() ?? '';
    }

    public function getTaxGroupText(): string
    {
        return strtoupper((string) $this->tax_group?->value);
    }

    public function getRiskRatioText(): string
    {
        return $this->risk_ratio?->label() ?? '';
    }

    public function getRiskRatioValue(): float
    {
        return $this->risk_ratio?->value() ?? 0.0;
    }

    public function isResign(): bool
    {
        $now = now();
        if (!$this->resign_date) {
            return false;
        }

        return $this->resign_date->lessThanOrEqualTo($now);
    }

    public function setCode(string $value): void
    {
        $this->attributes['code'] = StringUtil::uppercase($value);
    }

    public function setFullName(string $value): void
    {
        $this->attributes['full_name'] = StringUtil::uppercase($value);
    }

    public function setPassword(string $value): void
    {
        $this->attributes['password'] = $value;
    }

    public function getAuthPassword(): string
    {
        return (string) $this->password;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->code, $this->full_name);
    }
}