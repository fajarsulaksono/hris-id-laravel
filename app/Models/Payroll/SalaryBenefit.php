<?php

namespace App\Models\Payroll;

use App\Domain\Encryptor\SalaryCast;
use App\Models\Employee\Employee;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryBenefit extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'salary_benefits';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'component_id',
        'benefit_value',
        'benefit_key',
    ];

    protected $casts = [
        'benefit_value' => SalaryCast::class
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'component_id');
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