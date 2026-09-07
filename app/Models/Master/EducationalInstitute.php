<?php

namespace App\Models\Master;

use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationalInstitute extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'educational_institutes';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
    ];
}