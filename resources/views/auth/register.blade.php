@extends('layouts.auth')

@section('title', 'Daftar - ' . config('app.name'))

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

    <h1 class="h4 fw-bold mb-1">Buat akun baru</h1>
    <p class="text-muted small mb-4">Daftarkan diri Anda sebagai karyawan.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label for="full_name" class="form-label small fw-medium">Nama Lengkap</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-user"></i></span>
                <input type="text" class="form-control" id="full_name" name="full_name"
                       value="{{ old('full_name') }}" required autofocus>
            </div>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-7">
                <label for="date_of_birth" class="form-label small fw-medium">Tanggal Lahir</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="ti ti-calendar"></i></span>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                           value="{{ old('date_of_birth') }}" required>
                </div>
            </div>
            <div class="col-5">
                <label for="gender" class="form-label small fw-medium">Jenis Kelamin</label>
                <select class="form-select" id="gender" name="gender" required>
                    <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                    <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label for="identity_number" class="form-label small fw-medium">No. Identitas (NIK)</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-id-badge"></i></span>
                <input type="text" class="form-control" id="identity_number" name="identity_number"
                       value="{{ old('identity_number') }}" maxlength="27" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label small fw-medium">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-mail"></i></span>
                <input type="email" class="form-control" id="email" name="email"
                       value="{{ old('email') }}" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="username" class="form-label small fw-medium">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-at"></i></span>
                <input type="text" class="form-control" id="username" name="username"
                       value="{{ old('username') }}" required>
            </div>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label small fw-medium">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password"
                       placeholder="Minimal 8 karakter" minlength="8" required>
            </div>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="ti ti-user-plus me-1"></i> Daftar
            </button>
        </div>
    </form>

    <p class="text-center small text-muted mt-4 mb-0">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="auth-link-muted fw-medium">Masuk</a>
    </p>
@endsection