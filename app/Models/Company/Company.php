<?php

namespace App\Models\Company;

use App\Models\Master\City;
use App\Models\Master\Region;
use App\Support\Concerns\Blameable;
use App\Support\StringUtil;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'companies';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'birth_day',
        'email',
        'tax_number',
    ];

    protected $casts = [
        'birth_day' => 'date',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CompanyAddress::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(CompanyDepartment::class);
    }

    public function setCode(string $value): void
    {
        $this->attributes['code'] = StringUtil::uppercase($value);
    }

    public function setName(string $value): void
    {
        $this->attributes['name'] = StringUtil::uppercase($value);
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->code, $this->name);
    }

    public function getParentNameAttribute(): ?string
    {
        return $this->parent?->name;
    }
}