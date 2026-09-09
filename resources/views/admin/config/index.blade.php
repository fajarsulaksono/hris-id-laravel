@extends('layouts.app')

@section('title', 'Konfigurasi - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Konfigurasi" subtitle="Nilai konfigurasi aplikasi saat ini"></x-crud.page-header>
@endsection

@section('content')
    <div class="alert alert-light border small">
        <i class="ti ti-info-circle me-1"></i>
        Nilai di bawah dibaca dari <code>config/hris.php</code> dan <code>.env</code>. Untuk mengubahnya,
        sesuaikan <code>.env</code> lalu jalankan <code>php artisan config:cache</code>.
    </div>

    <div class="row g-3">
        @foreach ($groups as $title => $items)
            <div class="col-lg-6">
                <x-card :header="$title">
                    <dl class="row small mb-0">
                        @foreach ($items as $label => $value)
                            <dt class="col-6 text-muted fw-normal">{{ $label }}</dt>
                            <dd class="col-6 text-truncate" title="{{ $value }}">{{ $value }}</dd>
                        @endforeach
                    </dl>
                </x-card>
            </div>
        @endforeach
    </div>
@endsection
