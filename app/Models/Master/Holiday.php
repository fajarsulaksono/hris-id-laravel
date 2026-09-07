<?php

namespace App\Models\Master;

use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'holidays';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'holiday_date',
        'name',
    ];

    protected $casts = [
        'holiday_date' => 'date',
    ];
}