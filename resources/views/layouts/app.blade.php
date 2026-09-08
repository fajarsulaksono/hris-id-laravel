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
    <!-- DataTables 2.x -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.3.8/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            font-size: 15px;
            --bs-body-font-family: "Outfit", sans-serif;
            --bs-font-sans-serif: "Outfit", sans-serif;
            --bs-midnight: #151a2d;
            --bs-orange: #fd7e14;
        }
        .text-bg-midnight {
            color: #fff !important;
            background-color: var(--bs-midnight) !important;
        }
        .text-bg-orange {
            color: #fff !important;
            background-color: var(--bs-orange) !important;
        }
        body {
            font-family: "Outfit", sans-serif;
        }
        .sidebar-menu .nav-icon {
            font-size: 1.4rem;
            min-width: 1.4rem;
            max-width: 1.4rem;
        }
        .app-header .navbar-nav .ti {
            font-size: 1.4rem;
        }
        .app-header #theme-toggle .ti,
        .app-header .nav-link [data-lte-icon] {
            font-size: 1.3rem;
        }
        .sidebar-overlay {
            display: none !important;
        }
        .app-sidebar .sidebar-brand {
            border-bottom: none;
            background-color: darkred;
        }
        .app-sidebar .sidebar-brand .brand-image {
            display: none;
        }
        .app-sidebar .sidebar-brand .brand-text-short {
            display: none;
            visibility: hidden;
        }
        .sidebar-mini.sidebar-collapse .app-sidebar .sidebar-brand .brand-text-short {
            display: block;
            visibility: visible;
            color: #fff;
        }
        .sidebar-mini.sidebar-collapse .app-sidebar .sidebar-brand .brand-image {
            display: none !important;
        }
        .sidebar-mini.sidebar-collapse:not(.sidebar-without-hover) .app-sidebar:hover .sidebar-brand .brand-image {
            display: none;
        }
    </style>
    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-mini bg-body-tertiary">
<div class="app-wrapper">

    <!-- Navbar -->
    <nav class="app-header navbar navbar-expand text-bg-midnight" data-bs-theme="dark">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-lte-toggle="fullscreen" role="button" aria-label="Toggle fullscreen">
                        <i data-lte-icon="maximize" class="ti ti-arrows-maximize"></i>
                        <i data-lte-icon="minimize" class="ti ti-arrows-minimize d-none"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="theme-toggle" role="button" aria-label="Toggle theme">
                        <i id="theme-icon-light" class="ti ti-moon"></i>
                        <i id="theme-icon-dark" class="ti ti-sun d-none"></i>
                    </a>
                </li>
                <li class="nav-item dropdown user-menu">
                    <a class="nav-link" data-bs-toggle="dropdown" href="#">
                        <i class="ti ti-user-circle"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                        <li class="user-header text-bg-primary">
                            <i class="ti ti-user-circle" style="font-size: 5.5rem; line-height: 1.2;"></i>
                            <p>
                                {{ auth()->user()->full_name }}
                                <small>Member since {{ auth()->user()->created_at?->format('M. Y') }}</small>
                            </p>
                        </li>
                        <li class="user-body">
                            <div class="row">
                                <div class="col-4 text-center">
                                    <a href="#">Follow</a>
                                </div>
                                <div class="col-4 text-center">
                                    <a href="#">Sales</a>
                                </div>
                                <div class="col-4 text-center">
                                    <a href="#">Friends</a>
                                </div>
                            </div>
                        </li>
                        <li class="user-footer">
                            <a href="#" class="btn btn-outline-secondary">Profile</a>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline float-end">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="ti ti-logout me-1"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Sidebar -->
    <aside class="app-sidebar text-bg-midnight shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}" class="brand-link">
                <img src="{{ asset('images/hris-logo.svg') }}" alt="HRIS Logo" class="brand-image opacity-75 shadow">
                <span class="brand-text fw-bold fst-italic">HRIS Indonesia</span>
                <span class="brand-text-short fw-bold fst-italic">HRIS</span>
            </a>
        </div>

        <div class="sidebar-search mt-3" role="search">
            <label for="sidebar-search-input" class="visually-hidden">Filter menu</label>
            <input type="search" id="sidebar-search-input" class="form-control form-control-sm" placeholder="Filter menu…" autocomplete="off" data-lte-toggle="sidebar-search" data-lte-target="#navigation">
            <p class="fs-7 text-secondary mt-2 mb-0" data-lte-search-empty="" role="status" hidden>
                No matching pages.
            </p>
        </div>

        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false" id="navigation">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link">
                            <i class="nav-icon ti ti-dashboard"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    @can('view_master')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-database"></i>
                            <p>
                                Master
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('master') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endcan

                    @can('view_company')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-building"></i>
                            <p>
                                Perusahaan
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('company') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endcan

                    @can('view_employee')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-users"></i>
                            <p>
                                Karyawan
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('employee') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endcan

                    @can('view_address')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-map-pin"></i>
                            <p>Alamat</p>
                        </a>
                    </li>
                    @endcan

                    @can('view_attendance')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-clock-hour-4"></i>
                            <p>
                                Absensi
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('attendance') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                            <li class="nav-item">
                                <a href="{{ route('admin.attendance.attendances.upload') }}" class="nav-link">
                                    <i class="ti ti-upload nav-icon"></i>
                                    <p>Upload CSV</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.attendance.attendances.process') }}" class="nav-link">
                                    <i class="ti ti-calendar-stats nav-icon"></i>
                                    <p>Proses Bulanan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.attendance.attendances.recap') }}" class="nav-link">
                                    <i class="ti ti-report-analytics nav-icon"></i>
                                    <p>Rekap</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('view_overtime')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-clock-bolt"></i>
                            <p>
                                Lembur
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('overtime') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                            <li class="nav-item">
                                <a href="{{ route('admin.overtime.overtimes.upload') }}" class="nav-link">
                                    <i class="ti ti-upload nav-icon"></i>
                                    <p>Upload CSV</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.overtime.overtimes.process') }}" class="nav-link">
                                    <i class="ti ti-calendar-stats nav-icon"></i>
                                    <p>Proses Bulanan</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('view_leave')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-plane"></i>
                            <p>
                                Cuti
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('leave') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endcan

                    @can('view_payroll')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-wallet"></i>
                            <p>
                                Penggajian
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('payroll') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                            <li class="nav-item">
                                <a href="{{ route('admin.payroll.payrolls.process') }}" class="nav-link">
                                    <i class="ti ti-calendar-stats nav-icon"></i>
                                    <p>Proses Payroll</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.payroll.payrolls.tax') }}" class="nav-link">
                                    <i class="ti ti-report-money nav-icon"></i>
                                    <p>Proses Pajak</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('view_user')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-user-shield"></i>
                            <p>Manajemen User</p>
                        </a>
                    </li>
                    @endcan

                    @can('view_config')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-settings"></i>
                            <p>Konfigurasi</p>
                        </a>
                    </li>
                    @endcan
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content -->
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                @yield('content-header')
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="app-footer">
        <strong>HRIS Indonesia</strong> &copy; {{ date('Y') }}
    </footer>
</div>

<!-- Bootstrap 5.3 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/js/adminlte.min.js"></script>
<!-- DataTables 2.x -->
<script src="https://cdn.jsdelivr.net/npm/datatables.net@2.3.8/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.3.8/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('js/hris-master.js') }}"></script>
<script>
    (function () {
        const root = document.documentElement;
        const storageKey = 'lte-theme';
        const iconLight = document.getElementById('theme-icon-light');
        const iconDark = document.getElementById('theme-icon-dark');

        function applyTheme(theme) {
            const resolved = theme === 'dark' ? 'dark' : 'light';
            root.setAttribute('data-bs-theme', resolved);
            root.style.colorScheme = resolved;
            iconLight.classList.toggle('d-none', resolved === 'dark');
            iconDark.classList.toggle('d-none', resolved === 'light');
        }

        let stored = null;
        try {
            stored = localStorage.getItem(storageKey);
        } catch (e) {}

        const authored = root.getAttribute('data-bs-theme');
        let initial = stored;
        if (initial !== 'dark' && initial !== 'light') {
            initial = (authored === 'dark' || authored === 'light') ? authored
                : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        }
        applyTheme(initial);

        document.getElementById('theme-toggle').addEventListener('click', function (e) {
            e.preventDefault();
            const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            try {
                localStorage.setItem(storageKey, next);
            } catch (err) {}
        });
    })();
</script>
@stack('scripts')
</body>
</html>
