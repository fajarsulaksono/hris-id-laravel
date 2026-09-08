<?php

namespace App\Models\Payroll;

use App\Domain\Encryptor\SalaryCast;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollDetail extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'payroll_details';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'payroll_id',
        'component_id',
        'benefit_value',
        'benefit_key',
    ];

    protected $casts = [
        'benefit_value' => SalaryCast::class
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'component_id');
    }
}