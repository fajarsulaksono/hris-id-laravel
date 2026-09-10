<?php

namespace App\Models;

use App\Models\Employee\Employee;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    use HasUuids;

    protected $fillable = ['employee_id', 'token', 'platform'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
