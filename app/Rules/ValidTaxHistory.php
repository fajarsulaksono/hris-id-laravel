<?php

namespace App\Rules;

use App\Domain\Tax\Service\ValidateTaxHistory;
use App\Models\Tax\TaxGroupHistory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Port dari SemartHris\Validator\ValidTaxHistoryValidator:
 * riwayat data pajak hanya valid bila ada perubahan nyata (tax group / risk ratio).
 */
class ValidTaxHistory implements ValidationRule
{
    public function __construct(private readonly TaxGroupHistory $history)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! ValidateTaxHistory::validate($this->history)) {
            $fail('Tidak ada perubahan data pajak yang valid (tax group / risk ratio).');
        }
    }
}