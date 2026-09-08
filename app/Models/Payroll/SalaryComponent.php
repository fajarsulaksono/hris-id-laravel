<?php

namespace App\Models\Payroll;

use App\Enums\SalaryState;
use App\Support\Concerns\Blameable;
use App\Support\StringUtil;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryComponent extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'salary_components';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'state',
        'fixed',
    ];

    protected $casts = [
        'state' => SalaryState::class,
        'fixed' => 'boolean',
    ];

    public function benefits(): HasMany
    {
        return $this->hasMany(SalaryBenefit::class, 'component_id');
    }

    public function setCode(string $value): void
    {
        $this->attributes['code'] = StringUtil::uppercase($value);
    }

    public function setName(string $value): void
    {
        $this->attributes['name'] = StringUtil::uppercase($value);
    }

    public function getStateText(): string
    {
        return $this->state?->label() ?? '';
    }

    public function getStateTextAttribute(): string
    {
        return $this->getStateText();
    }

    public function getComponentNameAttribute(): string
    {
        return $this->name;
    }
}