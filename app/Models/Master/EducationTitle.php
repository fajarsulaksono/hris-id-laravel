<?php

namespace App\Models\Master;

use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationTitle extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'education_titles';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'short_name',
        'name',
    ];
}