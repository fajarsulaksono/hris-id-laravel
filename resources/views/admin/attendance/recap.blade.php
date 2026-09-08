@extends('layouts.app')

@section('title', 'Rekap Absensi - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Rekap Absensi" subtitle="Rekap per karyawan termasuk hari libur & akhir pekan">
        <x-slot:actions>
            <a href="{{ route('admin.attendance.attendances.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-back me-1"></i> Kembali
            </a>
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <form method="GET" action="{{ route('admin.attendance.attendances.recap') }}" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label for="month" class="form-label mb-0 fw-medium">Bulan</label>
            <select class="form-select form-select-sm" id="month" name="month">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" @selected($i === $month)>Bulan {{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="col-auto">
            <label for="year" class="form-label mb-0 fw-medium">Tahun</label>
            <input type="number" class="form-control form-control-sm" id="year" name="year" min="2000" max="2099" value="{{ $year }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="ti ti-filter me-1"></i> Tampilkan
            </button>
        </div>
    </form>

    <div class="row g-3">
        <div class="col-lg-8">
            <x-card header="Ringkasan per Karyawan">
                @forelse ($summaries as $summary)
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 @if ($loop->last) border-0 mb-0 pb-0 @endif">
                        <div>
                            <div class="fw-semibold">{{ $summary->employee_name ?? '-' }}</div>
                            <div class="text-muted small">Hari kerja: {{ $summary->total_workday }}</div>
                        </div>
                        <div class="text-end small">
                            <div><span class="text-success">Hadir {{ $summary->total_in }}</span> &middot;
                                <span class="text-danger">Absen {{ $summary->total_absent }}</span></div>
                            <div class="text-muted">Loyalitas {{ $summary->total_loyality }} &middot; Lembur {{ $summary->total_overtime }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Belum ada rekap untuk periode ini. Jalankan proses bulanan terlebih dahulu.</p>
                @endforelse
            </x-card>
        </div>

        <div class="col-lg-4">
            <x-card header="Hari Libur & Akhir Pekan">
                <div class="text-muted small mb-2">Akhir pekan (tidak dihitung hari kerja): <strong>Sabtu/Minggu</strong>.</div>
                @forelse ($holidays as $date)
                    <div class="d-flex align-items-center gap-2 border-bottom py-1 @if ($loop->last) border-0 @endif">
                        <i class="ti ti-calendar-day text-bg-orange rounded p-1"></i>
                        <span>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0 small">Tidak ada hari libur nasional pada periode ini.</p>
                @endforelse
            </x-card>
        </div>
    </div>
@endsection