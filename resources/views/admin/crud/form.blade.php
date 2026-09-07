@extends('layouts.app')

@section('title', ($edit ?? false) ? 'Ubah '.$module['title'].' - '.config('app.name') : 'Tambah '.$module['title'].' - '.config('app.name'))

@section('content-header')
    <x-crud.page-header :title="($edit ?? false) ? 'Ubah '.$module['title'] : 'Tambah '.$module['title']" subtitle="Lengkapi data {{ strtolower($module['title']) . (($edit ?? false) && $model ? ' - '.$model->name : '') }}">
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

    <x-card>
        <form method="POST" action="{{ $formAction }}" novalidate>
            @csrf
            @if ($formMethod === 'PUT')
                @method('PUT')
            @endif

            @foreach ($module['fields'] as $field)
                <x-crud.form-field :field="$field" :model="$model" />
            @endforeach

            <hr class="my-4">
            <x-crud.form-actions :back-url="$backUrl" :submit-text="($edit ?? false) ? 'Simpan Perubahan' : 'Simpan'" />
        </form>
    </x-card>
@endsection