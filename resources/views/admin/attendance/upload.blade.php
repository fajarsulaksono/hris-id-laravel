@extends('layouts.app')

@section('title', 'Upload Absensi - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Upload Absensi (CSV)" subtitle="Impor data absensi berbasis template CSV">
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
                        <div class="form-text">Kolom wajib: <code>employee_code</code>, <code>date</code>, <code>check_in</code>, <code>check_out</code>; opsional: <code>reason_code</code>.</div>
                    </div>

                    <x-crud.form-actions submit-text="Impor Absensi" submit-icon="ti-upload" />
                </form>
            </x-card>

            <x-card header="Contoh Template">
                <p class="text-muted small mb-2">Format tanggal mengikuti <code>{{ config('hris.format.date') }}</code> (contoh: <code>{{ now()->format(config('hris.format.date')) }}</code>).</p>
                <pre class="bg-body-secondary rounded p-3 mb-0"><code>employee_code,date,check_in,check_out,reason_code
EMP001,{{ now()->format(config('hris.format.date')) }},08:00,17:00,
EMP001,{{ now()->subDay()->format(config('hris.format.date')) }},,,
EMP002,{{ now()->format(config('hris.format.date')) }},,,{{ config('hris.attendance.default_absent_reason_code') }}</code></pre>
            </x-card>
        </div>
    </div>
@endsection