<?php

namespace App\Observers;

use App\Domain\Attendance\WorkshiftFinder;
use App\Domain\Attendance\WorkshiftSlicer;
use App\Models\Attendance\Workshift;

class WorkshiftObserver
{
    public function __construct(
        protected WorkshiftFinder $workshiftFinder,
        protected WorkshiftSlicer $workshiftSlicer,
    ) {
    }

    public function creating(Workshift $workshift): void
    {
        $this->slice($workshift);
    }

    public function updating(Workshift $workshift): void
    {
        $this->slice($workshift);
    }

    private function slice(Workshift $workshift): void
    {
        if ($sliceable = $this->workshiftFinder->findIntersection($workshift)) {
            $this->workshiftSlicer->slice($sliceable, $workshift);
        }
    }
}