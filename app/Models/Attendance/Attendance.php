<?php

namespace App\Models\Attendance;

use App\Models\Employee\Employee;
use App\Models\Master\Reason;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'attendances';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'shiftment_id',
        'attendance_date',
        'description',
        'check_in',
        'check_out',
        'early_in',
        'early_out',
        'late_in',
        'late_out',
        'absent',
        'reason_id',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'early_in' => 'integer',
        'early_out' => 'integer',
        'late_in' => 'integer',
        'late_out' => 'integer',
        'absent' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftment(): BelongsTo
    {
        return $this->belongsTo(Shiftment::class);
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(Reason::class);
    }

    public function getEmployeeNameAttribute(): ?string
    {
        return $this->employee?->display;
    }

    public function getShiftmentNameAttribute(): ?string
    {
        return $this->shiftment?->name;
    }

    public function getReasonNameAttribute(): ?string
    {
        return $this->reason?->name;
    }
}