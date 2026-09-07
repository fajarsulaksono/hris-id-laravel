<?php

namespace App\Models\Master;

use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'skills';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'skill_group_id',
        'name',
    ];

    public function skillGroup(): BelongsTo
    {
        return $this->belongsTo(SkillGroup::class);
    }

    public function getSkillGroupNameAttribute(): ?string
    {
        return $this->skillGroup?->name;
    }
}