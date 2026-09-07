@extends('layouts.app')

@section('title', ($holiday ? 'Ubah Hari Libur' : 'Tambah Hari Libur') . ' - ' . config('app.name'))

@section('content-header')
    <x-crud.page-header
        :title="$holiday ? 'Ubah Hari Libur' : 'Tambah Hari Libur'"
        subtitle="Hari libur dipakai untuk menghitung jam kerja efektif & lembur.">
        <x-slot:actions>
            <a href="{{ route('admin.master.holidays.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-list me-1"></i> Daftar
            </a>
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <x-crud.flash />
    <x-crud.form-errors />

    <x-card :header="$holiday ? 'Ubah Hari Libur' : 'Tambah Hari Libur'">
        <form method="POST"
              action="{{ $holiday ? route('admin.master.holidays.update', $holiday) : route('admin.master.holidays.store') }}">
            @csrf
            @if ($holiday)
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="holiday_date" class="form-label fw-medium">Tanggal</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="ti ti-calendar"></i></span>
                        <input type="date" class="form-control @error('holiday_date') is-invalid @enderror"
                               id="holiday_date" name="holiday_date"
                               value="{{ old('holiday_date', $holiday?->holiday_date?->format('Y-m-d')) }}" required>
                        @error('holiday_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-8 mb-3">
                    <label for="name" class="form-label fw-medium">Nama Hari Libur</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="ti ti-calendar-event"></i></span>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" maxlength="255"
                               value="{{ old('name', $holiday?->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <x-crud.form-actions back-url="{{ route('admin.master.holidays.index') }}"/>
        </form>
    </x-card>
@endsection