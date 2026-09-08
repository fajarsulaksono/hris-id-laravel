<?php

namespace App\Models\Payroll;

use App\Domain\Encryptor\SalaryCast;
use App\Models\Employee\Employee;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'payrolls';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'period_id',
        'take_home_pay',
        'take_home_pay_key',
    ];

    protected $casts = [
        'take_home_pay' => SalaryCast::class
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'period_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function getEmployeeNameAttribute(): ?string
    {
        return $this->employee?->display;
    }

    public function getPeriodLabelAttribute(): ?string
    {
        return $this->period?->display;
    }

    public function getDisplayAttribute(): string
    {
        return sprintf('%s - %s', $this->period?->display ?? '-', $this->employee?->display ?? '-');
    }
}