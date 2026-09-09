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
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Authenticatable implements HasMedia
{
    use Blameable;
    use CanResetPassword;
    use HasRoles;
    use HasUuids;
    use InteractsWithMedia;
    use Notifiable;
    use SoftDeletes;

    public const DEFAULT_ROLE = 'EMPLOYEE';

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

    public function careerHistories(): HasMany
    {
        return $this->hasMany(CareerHistory::class)->orderBy('created_at', 'desc');
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(Mutation::class)->orderBy('created_at', 'desc');
    }

    public function getEmployeeStatusText(): string
    {
        return $this->employee_status?->label() ?? '';
    }

    public function getEmployeeStatusTextAttribute(): string
    {
        return $this->getEmployeeStatusText();
    }

    public function getGenderText(): string
    {
        return $this->gender?->label() ?? '';
    }

    public function getGenderTextAttribute(): string
    {
        return $this->getGenderText();
    }

    public function getIdentityTypeText(): string
    {
        return $this->identity_type?->label() ?? '';
    }

    public function getIdentityTypeTextAttribute(): string
    {
        return $this->getIdentityTypeText();
    }

    public function getMaritalStatusText(): string
    {
        return $this->marital_status?->label() ?? '';
    }

    public function getMaritalStatusTextAttribute(): string
    {
        return $this->getMaritalStatusText();
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

    public function getDisplayAttribute(): string
    {
        return sprintf('%s - %s', $this->code, $this->full_name);
    }

    public function getAvatarAttribute(): string
    {
        $male = [
            'user1-128x128-male.jpg',
            'user2-160x160-male.jpg',
            'user6-128x128-male.jpg',
            'user8-128x128-male.jpg',
        ];

        $female = [
            'user3-128x128-female.jpg',
            'user5-128x128-female.jpg',
            'user7-128x128-female.jpg',
        ];

        $pool = $this->gender === Gender::FEMALE ? $female : $male;
        $index = abs(crc32((string) $this->id)) % count($pool);
        $name = $pool[$index] ?? $pool[0];

        return asset('images/avatar/'.$name);
    }

    public function getNameAttribute(): string
    {
        return $this->full_name ?? (string) $this->code;
    }

    public function getCompanyNameAttribute(): ?string
    {
        return $this->company?->name;
    }

    public function getDepartmentNameAttribute(): ?string
    {
        return $this->department?->name;
    }

    public function getJobLevelNameAttribute(): ?string
    {
        return $this->jobLevel?->name;
    }

    public function getJobTitleNameAttribute(): ?string
    {
        return $this->jobTitle?->name;
    }

    public function getSupervisorNameAttribute(): ?string
    {
        return $this->supervisor?->display;
    }

    public function getContractNameAttribute(): ?string
    {
        return $this->contract?->display;
    }

    public function getRegionOfBirthNameAttribute(): ?string
    {
        return $this->regionOfBirth?->name;
    }

    public function getCityOfBirthNameAttribute(): ?string
    {
        return $this->cityOfBirth?->name;
    }

    public function isResign(): bool
    {
        $now = now();
        if (! $this->resign_date) {
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

    public function getProfilePhotoUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('profile');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile()
            ->useDisk((string) config('media-library.disk_name'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(160)
            ->height(160)
            ->nonQueued();
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
