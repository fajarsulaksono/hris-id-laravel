@extends('layouts.app')

@section('title', 'Profil Saya - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Profil Saya" subtitle="Data diri yang tercatat di sistem"></x-crud.page-header>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-4">
            <x-card>
                <div class="text-center mb-3">
                    <img src="{{ $employee->avatar }}" alt="{{ $employee->full_name }}"
                         class="rounded-circle shadow" style="width: 96px; height: 96px; object-fit: cover;">
                    <h5 class="mt-3 mb-0">{{ $employee->full_name }}</h5>
                    <div class="text-muted small">{{ $employee->code }}</div>
                    <div class="mt-2">
                        <span class="badge text-bg-secondary">{{ $employee->job_title_name ?? '-' }}</span>
                        @if ($employee->department_name)
                            <span class="badge text-bg-light border">{{ $employee->department_name }}</span>
                        @endif
                    </div>
                </div>
                <hr class="my-2">
                <dl class="row small mb-0">
                    <dt class="col-5">Jenis Kelamin</dt><dd class="col-7">{{ $employee->gender_text }}</dd>
                    <dt class="col-5">Status</dt><dd class="col-7">{{ $employee->employee_status_text }}</dd>
                    <dt class="col-5">Tanggal Bergabung</dt><dd class="col-7">{{ $employee->join_date?->format('d/m/Y') }}</dd>
                    <dt class="col-5">Atasan</dt><dd class="col-7">{{ $employee->supervisor?->name ?? '-' }}</dd>
                </dl>
            </x-card>
        </div>

        <div class="col-lg-8">
            <x-card header="Data Pribadi">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Tempat, Tanggal Lahir</dt>
                    <dd class="col-sm-9">{{ $employee->date_of_birth?->format('d/m/Y') }}</dd>
                    <dt class="col-sm-3">Jenis Identitas</dt>
                    <dd class="col-sm-9">{{ $employee->identity_type_text }} &middot; {{ $employee->identity_number }}</dd>
                    <dt class="col-sm-3">Status Perkawinan</dt>
                    <dd class="col-sm-9">{{ $employee->marital_status_text }}</dd>
                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $employee->email }}</dd>
                    <dt class="col-sm-3">Sisa Cuti</dt>
                    <dd class="col-sm-9">{{ $employee->leave_balance }} hari</dd>
                </dl>
            </x-card>

            @if ($employee->addresses->isNotEmpty())
                <x-card header="Alamat" class="mt-3">
                    @foreach ($employee->addresses as $address)
                        <div class="d-flex gap-2 border-bottom py-2 @if ($loop->last) border-0 @endif">
                            <i class="ti ti-map-pin mt-1 text-muted"></i>
                            <div>
                                <div>{{ $address->address }}</div>
                                <div class="text-muted small">{{ $address->city?->name ?? '-' }}</div>
                            </div>
                        </div>
                    @endforeach
                </x-card>
            @endif
        </div>
    </div>
@endsection
