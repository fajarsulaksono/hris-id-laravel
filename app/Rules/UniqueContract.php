<?php

namespace App\Rules;

use App\Domain\Contract\CheckContract;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Port dari SemartHris\Validator\UniqueContractValidator:
 * kontrak yang dipilih tidak boleh dipakai entitas lain.
 *
 * Dipakai pada field contract_id, mis. saat mengisi placement / riwayat karyawan.
 *
 * @example new UniqueContract($placement?->getKey())
 */
class UniqueContract implements ValidationRule
{
    private CheckContract $service;

    public function __construct(
        private readonly ?string $excludeId = null,
        ?CheckContract $service = null,
    ) {
        $this->service = $service ?? app(CheckContract::class);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if ($this->service->isAlreadyUsed((string) $value, $this->excludeId)) {
            $fail('Kontrak tersebut sudah digunakan oleh data lain.');
        }
    }
}