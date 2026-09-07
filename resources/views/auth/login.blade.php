@extends('layouts.auth')

@section('title', 'Masuk - ' . config('app.name'))

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1 class="h4 fw-bold mb-1">Selamat datang kembali</h1>
    <p class="text-muted small mb-4">Masuk untuk memulai sesi Anda.</p>

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label small fw-medium">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-user"></i></span>
                <input type="text" class="form-control" id="username" name="username"
                       placeholder="Nama pengguna" value="{{ old('username') }}" required autofocus>
            </div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label small fw-medium">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password"
                       placeholder="Kata sandi" required>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label small" for="remember">Ingat saya</label>
            </div>
            <a href="{{ route('password.request') }}" class="small auth-link-muted">Lupa password?</a>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="ti ti-login me-1"></i> Masuk
            </button>
        </div>
    </form>

    <p class="text-center small text-muted mt-4 mb-0">
        Belum punya akun?
        <a href="{{ route('register') }}" class="auth-link-muted fw-medium">Daftar</a>
    </p>
@endsection