<?php

namespace App\Models\Employee;

use App\Enums\FamilyRelation;
use App\Enums\Gender;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeFamily extends Model
{
    use Blameable;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'employee_families';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'relation',
        'name',
        'gender',
        'date_of_birth',
        'identity_number',
        'job',
    ];

    protected $casts = [
        'relation' => FamilyRelation::class,
        'gender' => Gender::class,
        'date_of_birth' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getEmployeeNameAttribute(): ?string
    {
        return $this->employee?->display;
    }

    public function getRelationTextAttribute(): string
    {
        return $this->relation?->label() ?? '';
    }

    public function getGenderTextAttribute(): string
    {
        return $this->gender?->label() ?? '';
    }
}
