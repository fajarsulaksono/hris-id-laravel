<?php

namespace App\Models\Company;

use App\Models\Master\City;
use App\Models\Master\Region;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAddress extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'company_addresses';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'company_id',
        'address',
        'region_id',
        'city_id',
        'postal_code',
        'phone_number',
        'fax_number',
        'default_address',
    ];

    protected $casts = [
        'default_address' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function getCompanyNameAttribute(): ?string
    {
        return $this->company?->name;
    }

    public function getRegionNameAttribute(): ?string
    {
        return $this->region?->name;
    }

    public function getCityNameAttribute(): ?string
    {
        return $this->city?->name;
    }
}