<?php

namespace App\Models\Attendance;

use App\Models\Employee\Employee;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceSummary extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'attendance_summaries';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'year',
        'month',
        'total_workday',
        'total_in',
        'total_loyality',
        'total_absent',
        'total_overtime',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_workday' => 'integer',
        'total_in' => 'integer',
        'total_loyality' => 'integer',
        'total_absent' => 'integer',
        'total_overtime' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}