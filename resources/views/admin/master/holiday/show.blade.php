@extends('layouts.app')

@section('title', $holiday->name . ' - ' . config('app.name'))

@section('content-header')
    <x-crud.page-header :title="$holiday->name" subtitle="Detail hari libur">
        <x-slot:actions>
            <a href="{{ route('admin.master.holidays.edit', $holiday) }}" class="btn btn-primary">
                <i class="ti ti-pencil me-1"></i> Ubah
            </a>
            <a href="{{ route('admin.master.holidays.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-list me-1"></i> Daftar
            </a>
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <x-card :header="'Detail Hari Libur'">
        <dl class="row mb-0">
            <dt class="col-sm-3">Tanggal</dt>
            <dd class="col-sm-9">{{ $holiday->holiday_date->format('d-m-Y') }}</dd>

            <dt class="col-sm-3">Nama</dt>
            <dd class="col-sm-9">{{ $holiday->name }}</dd>
        </dl>
    </x-card>
@endsection