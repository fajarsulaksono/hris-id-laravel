@extends('layouts.app')

@section('title', 'Absensi Saya - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Absensi Saya" subtitle="Riwayat kehadiran Anda"></x-crud.page-header>
@endsection

@section('content')
    <x-card>
        @forelse ($rows as $attendance)
            @php $absent = (bool) $attendance->absent; @endphp
            <div class="d-flex justify-content-between align-items-center border-bottom py-2 @if ($loop->last) border-0 @endif">
                <div class="d-flex align-items-center gap-3">
                    <i class="ti ti-calendar-event fs-3 {{ $absent ? 'text-danger' : 'text-success' }}"></i>
                    <div>
                        <div class="fw-medium">{{ $attendance->attendance_date?->format('d/m/Y') }}</div>
                        <div class="text-muted small">
                            @if ($absent)
                                Absen &middot; {{ $attendance->reason?->name ?? '-' }}
                            @else
                                Shift {{ $attendance->shiftment?->name ?? '-' }}
                                &middot; Masuk {{ $attendance->check_in }} &middot; Keluar {{ $attendance->check_out }}
                            @endif
                        </div>
                    </div>
                </div>
                <span class="badge {{ $absent ? 'text-bg-danger' : 'text-bg-success' }}">
                    {{ $absent ? 'ABSEN' : 'HADIR' }}
                </span>
            </div>
        @empty
            <p class="text-muted mb-0">Belum ada catatan kehadiran.</p>
        @endforelse

        <div class="mt-3 d-flex justify-content-center">
            {{ $rows->links('pagination::bootstrap-5') }}
        </div>
    </x-card>
@endsection
