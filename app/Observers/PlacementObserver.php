<?php

namespace App\Observers;

use App\Domain\Job\CareerHistoryService;
use App\Models\Employee\Placement;

class PlacementObserver
{
    public function __construct(protected CareerHistoryService $careerHistoryService)
    {
    }

    /**
     * Catat riwayat karir 'PENEMPATAN'.
     */
    public function created(Placement $placement): void
    {
        $this->careerHistoryService->recordForPlacement($placement);
    }
}