<?php

namespace App\Models\Company;

use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyDepartment extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'company_departments';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'department_id',
        'company_id',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getCompanyNameAttribute(): ?string
    {
        return $this->company?->name;
    }

    public function getDepartmentNameAttribute(): ?string
    {
        return $this->department?->name;
    }
}