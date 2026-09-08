<?php

namespace App\Observers;

use App\Domain\Leave\SetAbsentWhenLeaveIsSubmited;
use App\Models\Attendance\Leave;

class LeaveObserver
{
    public function __construct(protected SetAbsentWhenLeaveIsSubmited $setAbsentWhenLeaveIsSubmited)
    {
    }

    public function created(Leave $leave): void
    {
        $this->setAbsentWhenLeaveIsSubmited->setAbsent($leave);
    }

    public function updating(Leave $leave): void
    {
        $this->setAbsentWhenLeaveIsSubmited->setAbsent($leave);
    }
}