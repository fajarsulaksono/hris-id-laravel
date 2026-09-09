@extends('layouts.app')

@section('title', 'Manajemen User - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Manajemen User" subtitle="Kelola akun login karyawan"></x-crud.page-header>
@endsection

@section('content')
    <x-crud.flash />

    <x-card header="Daftar Akun" flush>
        <div class="table-responsive">
            <table id="users-datatable" class="table table-hover align-middle"
                   data-dt-url="{{ $dataUrl }}" data-dt-trashed="0">
                <thead>
                <tr>
                    @foreach (json_decode($columnsJson, true) as $column)
                        <th @if (($column['data'] ?? null) === 'actions') class="text-end" @endif>{{ $column['title'] }}</th>
                    @endforeach
                </tr>
                </thead>
            </table>
        </div>
    </x-card>

    @push('scripts')
        <script>
            window.hris.initMasterTable('#users-datatable', {!! $columnsJson !!});
        </script>
    @endpush
@endsection
