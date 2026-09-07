<?php

namespace App\Models\Company;

use App\Support\Concerns\Blameable;
use App\Support\StringUtil;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobTitle extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'job_titles';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'job_level_id',
        'code',
        'name',
    ];

    public function jobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class);
    }

    public function setCode(string $value): void
    {
        $this->attributes['code'] = StringUtil::uppercase($value);
    }

    public function setName(string $value): void
    {
        $this->attributes['name'] = StringUtil::uppercase($value);
    }

    public function getJobLevelNameAttribute(): ?string
    {
        return $this->jobLevel?->name;
    }
}