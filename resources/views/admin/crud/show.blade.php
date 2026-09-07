@extends('layouts.app')

@section('title', 'Detail '.$module['title'].' - '.config('app.name'))

@section('content-header')
    <x-crud.page-header :title="$module['title'].': '.$model->name" subtitle="Detail data {{ strtolower($module['title']) }}">
        <x-slot:actions>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-back me-1"></i> Kembali
            </a>
            @can('manage_'.$module['menu'])
                <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.edit', $model) }}" class="btn btn-primary">
                    <i class="ti ti-pencil me-1"></i> Ubah
                </a>
            @endcan
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <x-card :header="'Informasi '.$module['title']">
        <x-crud.show-fields :fields="$module['fields']" :model="$model" />
    </x-card>
@endsection