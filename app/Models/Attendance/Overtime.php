<?php

namespace App\Models\Attendance;

use App\Enums\ApprovalStatus;
use App\Models\Employee\Employee;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Overtime extends Model
{
    use Blameable;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'overtimes';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $fillable = [
        'employee_id',
        'shiftment_id',
        'overtime_date',
        'start_hour',
        'end_hour',
        'raw_value',
        'calculated_value',
        'holiday',
        'overday',
        'approved_by_id',
        'status',
        'description',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'raw_value' => 'float',
        'calculated_value' => 'float',
        'holiday' => 'boolean',
        'overday' => 'boolean',
        'status' => ApprovalStatus::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftment(): BelongsTo
    {
        return $this->belongsTo(Shiftment::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by_id');
    }

    public function getEmployeeNameAttribute(): ?string
    {
        return $this->employee?->display;
    }

    public function getShiftmentNameAttribute(): ?string
    {
        return $this->shiftment?->name;
    }

    public function getApprovedByNameAttribute(): ?string
    {
        return $this->approvedBy?->display;
    }

    public function getStatusLabelAttribute(): ?string
    {
        return $this->status?->label();
    }
}
