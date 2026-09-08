@extends('layouts.app')

@section('title', 'Profil '.$employee->name.' - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Profil Karyawan" subtitle="{{ $employee->display }}">
        <x-slot:actions>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-back me-1"></i> Kembali
            </a>
            @can('manage_employee')
                <a href="{{ route('admin.employee.employees.edit', $employee) }}" class="btn btn-outline-primary">
                    <i class="ti ti-pencil me-1"></i> Ubah
                </a>
                <a href="{{ route('admin.employee.employees.promotion', $employee) }}" class="btn btn-primary">
                    <i class="ti ti-arrow-up me-1"></i> Promosi / Demosi
                </a>
            @endcan
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    @php
        $photo = $employee->getFirstMediaUrl('profile', 'thumb') ?: $employee->getFirstMediaUrl('profile');
    @endphp

    <div class="row g-3">
        <div class="col-lg-4">
            <x-card>
                <div class="text-center">
                    @if ($photo)
                        <img src="{{ $photo }}" alt="Foto {{ $employee->name }}"
                             class="img-thumbnail rounded-circle mb-3" style="width: 160px; height: 160px; object-fit: cover;">
                    @else
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle text-bg-midnight mb-3"
                             style="width: 160px; height: 160px;">
                            <i class="ti ti-user" style="font-size: 4rem;"></i>
                        </div>
                    @endif

                    <h5 class="mb-1">{{ $employee->name }}</h5>
                    <div class="text-muted small mb-2">{{ $employee->code }}</div>

                    <span class="badge text-bg-{{ $employee->isResign() ? 'danger' : 'success' }}">
                        {{ $employee->employee_status_text }}
                    </span>
                </div>

                <hr>

                <dl class="row mb-0 fs-7">
                    <dt class="col-5 text-muted fw-normal">Username</dt>
                    <dd class="col-7">{{ $employee->username ?? '-' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Email</dt>
                    <dd class="col-7 text-truncate">{{ $employee->email ?? '-' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Bergabung</dt>
                    <dd class="col-7">{{ $employee->join_date?->format('d/m/Y') ?? '-' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Keluar</dt>
                    <dd class="col-7">{{ $employee->resign_date?->format('d/m/Y') ?? '-' }}</dd>
                </dl>
            </x-card>

            <x-card header="Alamat">
                @forelse ($employee->addresses as $address)
                    <div class="border-bottom pb-2 mb-2 @if ($loop->last) border-0 mb-0 pb-0 @endif">
                        @if ($address->default_address)
                            <span class="badge text-bg-primary mb-1">Utama</span>
                        @endif
                        <div>{{ $address->address }}</div>
                        <div class="text-muted small">{{ $address->city_name ?? '-' }}</div>
                        <div class="text-muted small">{{ $address->phone_number ? 'Telp: '.$address->phone_number : '' }}</div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Belum ada alamat.</p>
                @endforelse
            </x-card>
        </div>

        <div class="col-lg-8">
            <x-card header="Data Pribadi">
                <dl class="row mb-0 fs-7">
                    <dt class="col-sm-4 text-muted fw-normal">Jenis Kelamin</dt>
                    <dd class="col-sm-8">{{ $employee->gender_text }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Tanggal Lahir</dt>
                    <dd class="col-sm-8">{{ $employee->date_of_birth?->format('d/m/Y') ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Identitas</dt>
                    <dd class="col-sm-8">{{ $employee->identity_type_text }} - {{ $employee->identity_number ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Status Perkawinan</dt>
                    <dd class="col-sm-8">{{ $employee->marital_status_text }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Tempat Lahir</dt>
                    <dd class="col-sm-8">{{ trim(($employee->city_of_birth_name ?? '').' '.($employee->region_of_birth_name ?? '')) ?: '-' }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Kelompok Pajak</dt>
                    <dd class="col-sm-8">{{ $employee->tax_group_text }}</dd>
                </dl>
            </x-card>

            <x-card header="Posisi Saat Ini">
                <dl class="row mb-0 fs-7">
                    <dt class="col-sm-4 text-muted fw-normal">Perusahaan</dt>
                    <dd class="col-sm-8">{{ $employee->company_name ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Departemen</dt>
                    <dd class="col-sm-8">{{ $employee->department_name ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Level Jabatan</dt>
                    <dd class="col-sm-8">{{ $employee->job_level_name ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Jabatan</dt>
                    <dd class="col-sm-8">{{ $employee->job_title_name ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Atasan</dt>
                    <dd class="col-sm-8">{{ $employee->supervisor_name ?? '-' }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Kontrak</dt>
                    <dd class="col-sm-8">{{ $employee->contract_name ?? '-' }}</dd>
                </dl>
            </x-card>

            <x-card header="Riwayat Karir">
                @forelse ($employee->careerHistories as $history)
                    <div class="d-flex gap-3 border-bottom pb-3 mb-3 @if ($loop->last) border-0 mb-0 pb-0 @endif">
                        <div class="text-center text-muted flex-shrink-0" style="width: 72px;">
                            <i class="ti ti-calendar-event d-block fs-4"></i>
                            <span class="small">{{ $history->created_at?->format('d/m/Y') }}</span>
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $history->job_title_name ?? '-' }}</div>
                            <div class="text-muted small">
                                {{ collect([
                                    $history->company_name,
                                    $history->department_name,
                                    $history->job_level_name,
                                    $history->supervisor_name ? 'Atasan: '.$history->supervisor_name : null,
                                ])->filter()->implode(' - ') ?: '—' }}
                            </div>
                            <span class="badge text-bg-secondary mt-1">{{ $history->description }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Belum ada riwayat karir.</p>
                @endforelse
            </x-card>
        </div>
    </div>
@endsection