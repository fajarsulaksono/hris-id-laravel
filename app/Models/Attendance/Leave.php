<?php

namespace App\Models\Attendance;

use App\Enums\ApprovalStatus;
use App\Models\Employee\Employee;
use App\Models\Master\Reason;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use Blameable;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'leaves';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $fillable = [
        'employee_id',
        'leave_date',
        'reason_id',
        'amount',
        'description',
        'status',
        'approved_by_id',
    ];

    protected $casts = [
        'leave_date' => 'date',
        'amount' => 'integer',
        'status' => ApprovalStatus::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(Reason::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by_id');
    }

    public function getEmployeeNameAttribute(): ?string
    {
        return $this->employee?->display;
    }

    public function getReasonNameAttribute(): ?string
    {
        return $this->reason?->name;
    }

    public function getStatusLabelAttribute(): ?string
    {
        return $this->status?->label();
    }

    public function getApprovedByNameAttribute(): ?string
    {
        return $this->approvedBy?->display;
    }
}
