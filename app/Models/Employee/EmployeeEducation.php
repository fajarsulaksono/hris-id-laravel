<?php

namespace App\Models\Employee;

use App\Models\Master\EducationalInstitute;
use App\Models\Master\EducationTitle;
use App\Support\Concerns\Blameable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeEducation extends Model
{
    use Blameable;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'employee_educations';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'education_institute_id',
        'education_title_id',
        'year',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function educationInstitute(): BelongsTo
    {
        return $this->belongsTo(EducationalInstitute::class);
    }

    public function educationTitle(): BelongsTo
    {
        return $this->belongsTo(EducationTitle::class);
    }

    public function getEmployeeNameAttribute(): ?string
    {
        return $this->employee?->display;
    }

    public function getEducationInstituteNameAttribute(): ?string
    {
        return $this->educationInstitute?->name;
    }

    public function getEducationTitleNameAttribute(): ?string
    {
        return $this->educationTitle?->name;
    }
}
