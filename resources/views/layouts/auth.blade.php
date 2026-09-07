<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <!-- Google Font: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3/dist/css/bootstrap.min.css">
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.46.0/dist/tabler-icons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/css/adminlte.min.css">
    <style>
        :root {
            --bs-body-font-family: "Outfit", sans-serif;
            --bs-font-sans-serif: "Outfit", sans-serif;
        }
        body {
            font-family: "Outfit", sans-serif;
        }
        .auth-split {
            min-height: 100vh;
        }
        .auth-image {
            background-image: url({{ asset('images/auth-bg.jpg') }});
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .auth-image .auth-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(25, 25, 112, 0.72) 0%, rgba(88, 28, 135, 0.55) 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 3rem;
        }
        .auth-image .auth-brand {
            font-size: 2.1rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #fff;
        }
        .auth-image .auth-tagline {
            color: rgba(255, 255, 255, 0.82);
            max-width: 32rem;
            font-size: 1.02rem;
            font-weight: 300;
        }
        .auth-image .auth-feature {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.92rem;
            font-weight: 400;
        }
        .auth-panel {
            background-color: #fff;
        }
        .auth-panel-inner {
            max-width: 360px;
            width: 100%;
            margin: 0 auto;
        }
        .auth-logo {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.4px;
        }
        .auth-link-muted {
            color: #6b7280;
            text-decoration: none;
        }
        .auth-link-muted:hover {
            color: #111827;
            text-decoration: underline;
        }
        @media (max-width: 991.98px) {
            .auth-image {
                display: none !important;
            }
            .auth-panel {
                flex: 1 0 auto;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-body-tertiary">
<div class="auth-split d-flex flex-column flex-lg-row">

    <!-- Layout Left: 3/4 width -->
    <div class="auth-image d-none d-lg-block col-lg-9">
        <div class="auth-overlay">
            <div>
                <div class="auth-brand">HRIS <span class="fw-light">Indonesia</span></div>
                <p class="auth-tagline mt-2 mb-4">
                    Sistem Informasi Manajemen Sumber Daya Manusia — payroll, absensi,
                    BPJS, dan PPH21 untuk tenaga kerja Indonesia dalam satu platform.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <span class="auth-feature"><i class="ti ti-wallet"></i> Gaji &amp; PPH21</span>
                    <span class="auth-feature"><i class="ti ti-clock-hour-4"></i> Absensi &amp; Lembur</span>
                    <span class="auth-feature"><i class="ti ti-users"></i> Manajemen Karyawan</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Layout Right: 1/4 width -->
    <main class="auth-panel d-flex align-items-center justify-content-center col-lg-3 px-3 py-4">
        <div class="auth-panel-inner">
            <div class="text-center mb-4">
                <a href="{{ route('login') }}" class="auth-logo text-decoration-none">
                    <span class="text-primary">HRIS</span> <span class="text-dark fw-light">Indonesia</span>
                </a>
            </div>

            @yield('content')
        </div>
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>