@php
    $backUrl = $backUrl ?? url()->previous();
    $submitText = $submitText ?? 'Simpan';
    $submitIcon = $submitIcon ?? 'ti-device-floppy';
@endphp
<div class="d-flex gap-2">
    <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-back me-1"></i> Batal
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="{{ $submitIcon }} me-1"></i> {{ $submitText }}
    </button>
</div>