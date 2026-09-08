@extends('layouts.app')

@section('title', 'Proses Payroll - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Proses Payroll" subtitle="Hitung gaji (tunjangan tetap, lembur, BPJS, tunjangan/potongan) per periode">
        <x-slot:actions>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-back me-1"></i> Kembali
            </a>
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <x-card header="Periode Proses">
                <x-crud.flash />

                <form action="{{ $formAction }}" method="POST">
                    @csrf
                    <x-crud.form-errors />

                    <div class="mb-3">
                        <label for="company_id" class="form-label fw-medium">Perusahaan (opsional)</label>
                        <select class="form-select" id="company_id" name="company_id">
                            <option value="">Semua Perusahaan</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->getKey() }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Kosongkan untuk memproses seluruh karyawan aktif.</div>
                    </div>

                    <div class="mb-3">
                        <label for="month" class="form-label fw-medium">Bulan</label>
                        <select class="form-select" id="month" name="month">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" @selected($i === $month)>Bulan {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="year" class="form-label fw-medium">Tahun</label>
                        <input type="number" class="form-control" id="year" name="year" min="2000" max="2099" value="{{ $year }}" required>
                        <div class="form-text">Periode tidak boleh melewati bulan berjalan. Periode sebelumnya harus sudah diproses.</div>
                    </div>

                    <x-crud.form-actions submit-text="Proses Payroll" submit-icon="ti-calendar-stats" />
                </form>
            </x-card>
        </div>
    </div>
@endsection