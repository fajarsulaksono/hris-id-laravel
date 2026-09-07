<?php

namespace App\Models\Master;

use App\Enums\ReasonType;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reason extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'absent_reasons';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'type',
        'code',
        'name',
    ];

    protected $casts = [
        'type' => ReasonType::class,
    ];

    public function getTypeText(): string
    {
        return $this->type->label();
    }
}