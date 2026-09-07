<?php

namespace App\Domain\Contract;

use App\Models\Master\Contract;

/**
 * Port dari SemartHris\Component\Contract\Service\CheckContract:
 * memastikan sebuah kontrak hanya digunakan oleh satu entitas (karyawan, placement,
 * riwayat karier, mutasi, atau riwayat tunjangan).
 */
class CheckContract
{
    /**
     * @var array<class-string>
     */
    private array $contractableModels;

    /**
     * @param  array<class-string>  $contractableModels
     */
    public function __construct(array $contractableModels = [])
    {
        $this->contractableModels = $contractableModels ?: config('hris.contractables', []);
    }

    /**
     * Cek apakah kontrak sudah dipakai entitas lain.
     *
     * @param  string|null  $excludeId  id entitas yang sedang diedit (diabaikan)
     */
    public function isAlreadyUsed(string $contractId, ?string $excludeId = null): bool
    {
        foreach ($this->contractableModels as $model) {
            $query = $model::query()->where('contract_id', $contractId);

            if ($excludeId !== null && is_string($excludeId)) {
                $query->whereKeyNot($excludeId);
            }

            if ($query->exists()) {
                return true;
            }
        }

        return false;
    }

    public function markUsedContract(Contract $contract): void
    {
        $contract->used = true;
        $contract->save();
    }
}