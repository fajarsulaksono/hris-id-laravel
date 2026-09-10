<?php

namespace App\Observers;

use App\Domain\Tax\Service\SetNewTaxDataHistory;
use App\Domain\Tax\Service\SetOldTaxDataHistory;
use App\Models\Tax\TaxGroupHistory;

class TaxGroupHistoryObserver
{
    /**
     * Port SetOldTaxDataHistorySubscriber (prePersist): rekam nilai lama dari
     * data pajak karyawan saat ini.
     */
    public function creating(TaxGroupHistory $history): void
    {
        SetOldTaxDataHistory::setOldTaxData($history);
    }

    /**
     * Port SetNewTaxDataHistory (dipanggil prePersist): terapkan nilai baru
     * (tax group / risk ratio) ke karyawan setelah riwayat tersimpan.
     */
    public function created(TaxGroupHistory $history): void
    {
        SetNewTaxDataHistory::setNewTaxData($history);
    }

    /**
     * Port SetOldTaxDataHistorySubscriber (preUpdate): saat riwayat pajak
     * diedit, terapkan ulang nilai baru ke karyawan.
     */
    public function updated(TaxGroupHistory $history): void
    {
        SetNewTaxDataHistory::setNewTaxData($history);
    }
}
