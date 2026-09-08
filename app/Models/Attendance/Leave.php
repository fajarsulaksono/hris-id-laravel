<?php

namespace App\Models\Attendance;

use App\Models\Employee\Employee;
use App\Models\Master\Reason;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'leaves';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'leave_date',
        'reason_id',
        'amount',
        'description',
    ];

    protected $casts = [
        'leave_date' => 'date',
        'amount' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(Reason::class);
    }

    public function getEmployeeNameAttribute(): ?string
    {
        return $this->employee?->display;
    }

    public function getReasonNameAttribute(): ?string
    {
        return $this->reason?->name;
    }
}