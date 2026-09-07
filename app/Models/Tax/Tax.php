<?php

namespace App\Models\Tax;

use App\Enums\TaxGroup;
use App\Models\Employee\Employee;
use App\Models\Payroll\PayrollPeriod;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'taxs';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'period_id',
        'employee_id',
        'tax_group',
        'untaxable',
        'taxable',
        'tax_value',
        'tax_key',
    ];

    protected $casts = [
        'tax_group' => TaxGroup::class,
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}