<?php

namespace App\Models\Attendance;

use App\Support\Concerns\Blameable;
use App\Support\StringUtil;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shiftment extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'shiftments';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'start_hour',
        'end_hour',
    ];

    public function workshifts(): HasMany
    {
        return $this->hasMany(Workshift::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function setCode(string $value): void
    {
        $this->attributes['code'] = StringUtil::uppercase($value);
    }

    public function setName(string $value): void
    {
        $this->attributes['name'] = StringUtil::uppercase($value);
    }
}