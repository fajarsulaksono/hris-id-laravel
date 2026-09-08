<?php

namespace App\Models\Tax;

use App\Enums\RiskRatio;
use App\Enums\TaxGroup;
use App\Models\Employee\Employee;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxGroupHistory extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'tax_group_history';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'old_tax_group',
        'new_tax_group',
        'old_risk_ratio',
        'new_risk_ratio',
    ];

    protected $casts = [
        'old_tax_group' => TaxGroup::class,
        'new_tax_group' => TaxGroup::class,
        'old_risk_ratio' => RiskRatio::class,
        'new_risk_ratio' => RiskRatio::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getEmployeeNameAttribute(): ?string
    {
        return $this->employee?->display;
    }
}