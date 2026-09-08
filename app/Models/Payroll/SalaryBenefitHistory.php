<?php

namespace App\Models\Payroll;

use App\Domain\Encryptor\SalaryCast;
use App\Models\Employee\Employee;
use App\Models\Master\Contract;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryBenefitHistory extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'salary_benefit_histories';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'component_id',
        'contract_id',
        'new_benefit_value',
        'old_benefit_value',
        'benefit_key',
        'description',
    ];

    protected $casts = [
        'new_benefit_value' => SalaryCast::class,
        'old_benefit_value' => SalaryCast::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'component_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function getEmployeeNameAttribute(): ?string
    {
        return $this->employee?->display;
    }

    public function getComponentNameAttribute(): ?string
    {
        return $this->component?->name;
    }
}