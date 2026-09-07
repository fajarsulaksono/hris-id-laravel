<?php

namespace App\Models\Employee;

use App\Enums\MutationType;
use App\Models\Company\Company;
use App\Models\Company\Department;
use App\Models\Company\JobLevel;
use App\Models\Company\JobTitle;
use App\Models\Master\Contract;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mutation extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'job_mutations';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'type',
        'employee_id',
        'old_company_id',
        'old_department_id',
        'old_job_level_id',
        'old_job_title_id',
        'old_supervisor_id',
        'new_company_id',
        'new_department_id',
        'new_job_level_id',
        'new_job_title_id',
        'new_supervisor_id',
        'contract_id',
    ];

    protected $casts = [
        'type' => MutationType::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function oldCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'old_company_id');
    }

    public function oldDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'old_department_id');
    }

    public function oldJobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class, 'old_job_level_id');
    }

    public function oldJobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'old_job_title_id');
    }

    public function oldSupervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'old_supervisor_id');
    }

    public function newCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'new_company_id');
    }

    public function newDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'new_department_id');
    }

    public function newJobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class, 'new_job_level_id');
    }

    public function newJobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'new_job_title_id');
    }

    public function newSupervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'new_supervisor_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function getTypeText(): string
    {
        return $this->type?->label() ?? '';
    }
}