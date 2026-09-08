<?php

namespace App\Models\Payroll;

use App\Models\Company\Company;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPeriod extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'payroll_periods';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'company_id',
        'year',
        'month',
        'closed',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'closed' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'period_id');
    }

    public function getCompanyNameAttribute(): ?string
    {
        return $this->company?->name;
    }

    public function getDisplayAttribute(): string
    {
        return sprintf('%s-%s', $this->year, str_pad((string) $this->month, 2, '0', STR_PAD_LEFT));
    }
}