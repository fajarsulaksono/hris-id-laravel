<?php

namespace App\Models\Payroll;

use App\Models\Employee\Employee;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryAllowance extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'salary_allowances';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'component_id',
        'year',
        'month',
        'benefit_value',
        'benefit_key',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'component_id');
    }
}