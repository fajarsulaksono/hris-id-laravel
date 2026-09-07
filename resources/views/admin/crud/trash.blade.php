@extends('layouts.app')

@php $columns = json_decode($columnsJson, true); @endphp

@section('title', 'Sampah '.$module['title'].' - '.config('app.name'))

@section('content-header')
    <x-crud.page-header :title="'Sampah '.$module['title']" subtitle="Data {{ strtolower($module['title']) }} yang telah dihapus">
        <x-slot:actions>
            <a href="{{ $indexUrl }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-back me-1"></i> Kembali ke daftar
            </a>
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <x-crud.flash />

    <x-card :header="'Sampah '.$module['title']" flush>
        <div class="table-responsive">
            <table id="master-datatable" class="table table-hover align-middle"
                   data-dt-url="{{ $dataUrl }}"
                   data-dt-trashed="1">
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