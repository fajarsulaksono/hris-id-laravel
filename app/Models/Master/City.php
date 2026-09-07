<?php

namespace App\Models\Master;

use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'cities';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'region_id',
        'code',
        'name',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function getRegionNameAttribute(): ?string
    {
        return $this->region?->name;
    }

    public function getDisplayAttribute(): string
    {
        return sprintf('%s - %s', $this->code, $this->name);
    }
}