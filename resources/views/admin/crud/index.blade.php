@extends('layouts.app')

@php $columns = json_decode($columnsJson, true); @endphp

@section('title', $module['title'].' - '.config('app.name'))

@section('content-header')
    <x-crud.page-header :title="$module['title']" subtitle="Kelola data {{ strtolower($module['title']) }}">
        <x-slot:actions>
            @can('manage_'.$module['menu'])
                <a href="{{ $trashUrl }}" class="btn btn-outline-secondary">
                    <i class="ti ti-trash me-1"></i> Sampah
                </a>
                <a href="{{ $createUrl }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Tambah
                </a>
            @endcan
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <x-crud.flash />

    <x-card :header="'Daftar '.$module['title']" flush>
        <div class="table-responsive">
            <table id="master-datatable" class="table table-hover align-middle"
                   data-dt-url="{{ $dataUrl }}"
                   data-dt-trashed="0">
                <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th @if (($column['data'] ?? null) === 'actions') class="text-end" @endif>{{ $column['title'] }}</th>
                    @endforeach
                </tr>
                </thead>
            </table>
        </div>
    </x-card>

    @push('scripts')
        <script>
            window.hris.initMasterTable('#master-datatable', {!! $columnsJson !!});
        </script>
    @endpush
@endsection