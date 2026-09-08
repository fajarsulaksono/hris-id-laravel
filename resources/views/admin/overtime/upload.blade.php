@extends('layouts.app')

@section('title', 'Upload Lembur - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Upload Lembur (CSV)" subtitle="Impor data lembur berbasis template CSV">
        <x-slot:actions>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-back me-1"></i> Kembali
            </a>
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <x-card header="Unggah File CSV">
                <x-crud.flash />

                <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <x-crud.form-errors />

                    <div class="mb-3">
                        <label for="file" class="form-label fw-medium">File CSV <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" accept=".csv,.txt" required>
                        <div class="form-text">Kolom wajib: <code>employee_code</code>, <code>date</code>, <code>check_in</code>, <code>check_out</code>.</div>
                    </div>

                    <x-crud.form-actions submit-text="Impor Lembur" submit-icon="ti-upload" />
                </form>
            </x-card>

            <x-card header="Contoh Template">
                <p class="text-muted small mb-2">Format tanggal mengikuti <code>{{ config('hris.format.date') }}</code> (contoh: <code>{{ now()->format(config('hris.format.date')) }}</code>). Nilai lembur dihitung otomatis dari jam masuk/keluar & shift.</p>
                <pre class="bg-body-secondary rounded p-3 mb-0"><code>employee_code,date,check_in,check_out
EMP001,{{ now()->format(config('hris.format.date')) }},18:00,20:00
EMP002,{{ now()->subDay()->format(config('hris.format.date')) }},17:30,21:00</code></pre>
            </x-card>
        </div>
    </div>
@endsection