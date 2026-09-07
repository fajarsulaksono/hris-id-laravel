@extends('layouts.auth')

@section('title', 'Lupa Password - ' . config('app.name'))

@section('content')
    @if (session('status'))
        <div class="alert alert-success py-2">
            <p class="mb-0 small">{{ session('status') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1 class="h4 fw-bold mb-1">Lupa password?</h1>
    <p class="text-muted small mb-4">
        Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
    </p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label for="email" class="form-label small fw-medium">Alamat Email</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-mail"></i></span>
                <input type="email" class="form-control" id="email" name="email"
                       value="{{ old('email') }}" required autofocus>
            </div>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="ti ti-send me-1"></i> Kirim Tautan Reset
            </button>
        </div>
    </form>

    <p class="text-center small text-muted mt-4 mb-0">
        <a href="{{ route('login') }}" class="auth-link-muted fw-medium">
            <i class="ti ti-chevron-left"></i> Kembali ke halaman masuk
        </a>
    </p>
@endsection