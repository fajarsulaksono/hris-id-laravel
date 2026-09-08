<?php

namespace App\Models\Master;

use App\Enums\ContractType;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasUuids;
    use SoftDeletes;
    use Blameable;

    protected $table = 'contracts';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'type',
        'letter_number',
        'subject',
        'description',
        'start_date',
        'end_date',
        'signed_date',
        'tags',
        'used',
    ];

    protected $casts = [
        'type' => ContractType::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_date' => 'date',
        'tags' => 'array',
        'used' => 'boolean',
    ];

    public function getTypeText(): string
    {
        return $this->type->label();
    }

    public function getTypeTextAttribute(): string
    {
        return $this->getTypeText();
    }

    public function getDisplayAttribute(): string
    {
        return sprintf('%s - %s', $this->letter_number, $this->subject);
    }
}