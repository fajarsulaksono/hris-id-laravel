@extends('layouts.app')

@section('title', 'Cuti & Izin Saya - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Cuti & Izin Saya" subtitle="Ajukan dan pantau cuti/izin Anda"></x-crud.page-header>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-5">
            <x-card header="Ajukan Cuti / Izin">
                <div class="alert alert-light border small py-2">
                    Sisa saldo cuti Anda: <strong>{{ $balance }} hari</strong>.
                </div>
                <form method="POST" action="{{ route('my.leaves.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="leave_date" class="form-label fw-medium">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('leave_date') is-invalid @enderror" id="leave_date" name="leave_date" value="{{ old('leave_date') }}" required>
                        @error('leave_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="reason_id" class="form-label fw-medium">Alasan <span class="text-danger">*</span></label>
                        <select class="form-select @error('reason_id') is-invalid @enderror" id="reason_id" name="reason_id" required>
                            <option value="">---</option>
                            @foreach ($reasons as $reason)
                                <option value="{{ $reason->getKey() }}" @selected(old('reason_id') == $reason->getKey())>{{ $reason->name }}</option>
                            @endforeach
                        </select>
                        @error('reason_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label fw-medium">Jumlah Hari <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" min="1" max="31" value="{{ old('amount', 1) }}" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label fw-medium">Keterangan</label>
                        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-send me-1"></i> Ajukan
                    </button>
                </form>
            </x-card>
        </div>

        <div class="col-lg-7">
            <x-card header="Riwayat Pengajuan">
                @forelse ($rows as $leave)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2 @if ($loop->last) border-0 @endif">
                        <div class="d-flex align-items-center gap-3">
                            <i class="ti ti-plane fs-3 text-primary"></i>
                            <div>
                                <div class="fw-medium">{{ $leave->leave_date?->format('d/m/Y') }} &middot; {{ $leave->amount }} hari</div>
                                <div class="text-muted small">{{ $leave->reason?->name ?? '-' }} @if ($leave->description) &middot; {{ $leave->description }} @endif</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Belum ada pengajuan cuti/izin.</p>
                @endforelse

                <div class="mt-3 d-flex justify-content-center">
                    {{ $rows->links('pagination::bootstrap-5') }}
                </div>
            </x-card>
        </div>
    </div>
@endsection
