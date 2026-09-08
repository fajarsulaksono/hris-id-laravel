@extends('layouts.app')

@section('title', 'Promosi / Demosi - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Promosi / Demosi" subtitle="{{ $employee->display }}">
        <x-slot:actions>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-back me-1"></i> Kembali
            </a>
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <x-crud.flash />
    <x-crud.form-errors />

    <div class="row g-3">
        <div class="col-lg-4">
            <x-card header="Posisi Saat Ini">
                <dl class="row mb-0 fs-7">
                    <dt class="col-5 text-muted fw-normal">Perusahaan</dt>
                    <dd class="col-7">{{ $employee->company_name ?? '-' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Departemen</dt>
                    <dd class="col-7">{{ $employee->department_name ?? '-' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Level Jabatan</dt>
                    <dd class="col-7">{{ $employee->job_level_name ?? '-' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Jabatan</dt>
                    <dd class="col-7">{{ $employee->job_title_name ?? '-' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Atasan</dt>
                    <dd class="col-7">{{ $employee->supervisor_name ?? '-' }}</dd>
                </dl>
            </x-card>
        </div>

        <div class="col-lg-8">
            <x-card>
                <form method="POST" action="{{ route('admin.employee.employees.promotion.store', $employee) }}" novalidate>
                    @csrf

                    <div class="alert alert-info py-2 small">
                        <i class="ti ti-info-circle me-1"></i>
                        Posisi lama otomatis dicatat dari data karyawan. Riwayat karir baru akan dibuat setelah disimpan.
                    </div>

                    @foreach ($fields as $field)
                        <x-crud.form-field :field="$field" :model="null" />
                    @endforeach

                    <hr class="my-4">
                    <x-crud.form-actions :back-url="$backUrl" submit-text="Simpan Promosi / Demosi" />
                </form>
            </x-card>
        </div>
    </div>
@endsection