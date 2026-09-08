<?php

namespace App\Observers;

use App\Domain\Job\CareerHistoryService;
use App\Domain\Job\MutationApplier;
use App\Models\Employee\Mutation;

class MutationObserver
{
    public function __construct(
        protected MutationApplier $mutationApplier,
        protected CareerHistoryService $careerHistoryService,
    ) {
    }

    /**
     * Isi posisi lama (old_*) dari data karyawan sebelum mutasi tersimpan.
     */
    public function creating(Mutation $mutation): void
    {
        $this->mutationApplier->fillOldJob($mutation);
    }

    /**
     * Terapkan posisi baru ke data karyawan + catat riwayat karir.
     */
    public function created(Mutation $mutation): void
    {
        $this->mutationApplier->applyNewJob($mutation);

        $this->careerHistoryService->recordForMutation($mutation);
    }
}