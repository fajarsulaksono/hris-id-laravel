<?php

namespace App\Models\Employee;

use App\Enums\SkillLevel;
use App\Models\Master\Skill;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSkill extends Model
{
    use Blameable;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'employee_skills';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'skill_id',
        'level',
    ];

    protected $casts = [
        'level' => SkillLevel::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function getEmployeeNameAttribute(): ?string
    {
        return $this->employee?->display;
    }

    public function getSkillNameAttribute(): ?string
    {
        return $this->skill?->name;
    }

    public function getLevelTextAttribute(): string
    {
        return $this->level?->label() ?? '';
    }
}
