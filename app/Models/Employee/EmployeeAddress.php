<?php

namespace App\Models\Employee;

use App\Models\Master\City;
use App\Models\Master\Region;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAddress extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'employee_addresses';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}