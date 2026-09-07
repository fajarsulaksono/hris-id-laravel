@extends('layouts.auth')

@section('title', 'Reset Password - ' . config('app.name'))

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

    <h1 class="h4 fw-bold mb-1">Reset password</h1>
    <p class="text-muted small mb-4">Buat kata sandi baru untuk akun Anda.</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label for="email" class="form-label small fw-medium">Alamat Email</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-mail"></i></span>
                <input type="email" class="form-control" id="email" name="email"
                       value="{{ $email ?? old('email') }}" required autofocus>
            </div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label small fw-medium">Password Baru</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password"
                       placeholder="Minimal 8 karakter" minlength="8" required>
            </div>
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="form-label small fw-medium">Konfirmasi Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-lock-check"></i></span>
                <input type="password" class="form-control" id="password_confirmation"
                       name="password_confirmation" placeholder="Ulangi kata sandi" required>
            </div>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="ti ti-key me-1"></i> Perbarui Password
            </button>
        </div>
    </form>

    <p class="text-center small text-muted mt-4 mb-0">
        <a href="{{ route('login') }}" class="auth-link-muted fw-medium">
            <i class="ti ti-chevron-left"></i> Kembali ke halaman masuk
        </a>
    </p>
@endsection