<?php

namespace App\Domain\Attendance;

use App\Models\Attendance\Workshift;

class WorkshiftSlicer
{
    public function slice(Workshift $firstWorkshift, Workshift $secondWorkshift): void
    {
        if ($firstWorkshift->employee_id !== $secondWorkshift->employee_id) {
            return;
        }

        $firstStart = $firstWorkshift->start_date;
        $firstEnd = $firstWorkshift->end_date;
        $secondStart = $secondWorkshift->start_date;
        $secondEnd = $secondWorkshift->end_date;

        if ($firstStart->lt($secondStart) && $secondEnd->lt($firstEnd)) {
            $newFirstEnd = $secondStart->copy()->subDay();
            $thirdStart = $secondEnd->copy()->addDay();

            $firstWorkshift->end_date = $newFirstEnd;
            $firstWorkshift->save();

            Workshift::create([
                'employee_id' => $secondWorkshift->employee_id,
                'shiftment_id' => $firstWorkshift->shiftment_id,
                'start_date' => $thirdStart,
                'end_date' => $firstEnd,
                'description' => 'SLICED BY SYSTEM',
            ]);
        }
    }
}