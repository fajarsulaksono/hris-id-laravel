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
    <!-- HRIS Sidebar -->
    <link rel="stylesheet" href="{{ asset('css/hris-sidebar.css') }}">
    <!-- HRIS Role Theme -->
    <link rel="stylesheet" href="{{ asset('css/hris-role-theme.css') }}">
    <!-- HRIS Breadcrumb -->
    <link rel="stylesheet" href="{{ asset('css/hris-breadcrumb.css') }}">
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
        /* Row action (kolom "Aksi") buttons — compact icon pills */
        .row-actions {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }
        .row-actions form {
            display: inline-flex;
            margin: 0;
            padding: 0;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.875rem;
            height: 1.875rem;
            padding: 0;
            border: 1px solid transparent;
            border-radius: .55rem;
            font-size: 1.15rem;
            line-height: 1;
            text-decoration: none;
            transition: background-color .15s ease-in-out, color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }
        .btn-action:hover {
            text-decoration: none;
        }
        .btn-action:focus-visible {
            outline: 2px solid var(--bs-primary);
            outline-offset: 1px;
        }
        .btn-action i {
            pointer-events: none;
        }

        .btn-action-view { color: #5b6472; background: rgba(91, 100, 114, .12); border-color: rgba(91, 100, 114, .3); }
        .btn-action-view:hover { color: #fff; background: #5b6472; border-color: #5b6472; }

        .btn-action-profile { color: #0e7490; background: rgba(14, 116, 144, .12); border-color: rgba(14, 116, 144, .3); }
        .btn-action-profile:hover { color: #fff; background: #0e7490; border-color: #0e7490; }

        .btn-action-edit { color: #1d4ed8; background: rgba(29, 78, 216, .12); border-color: rgba(29, 78, 216, .3); }
        .btn-action-edit:hover { color: #fff; background: #1d4ed8; border-color: #1d4ed8; }

        .btn-action-delete { color: #b91c1c; background: rgba(185, 28, 28, .12); border-color: rgba(185, 28, 28, .3); }
        .btn-action-delete:hover { color: #fff; background: #b91c1c; border-color: #b91c1c; }

        .btn-action-restore { color: #15803d; background: rgba(21, 128, 61, .12); border-color: rgba(21, 128, 61, .3); }
        .btn-action-restore:hover { color: #fff; background: #15803d; border-color: #15803d; }

        [data-bs-theme="dark"] .btn-action-view { color: #cbd5e1; background: rgba(148, 163, 184, .16); border-color: rgba(148, 163, 184, .38); }
        [data-bs-theme="dark"] .btn-action-profile { color: #67e8f9; background: rgba(34, 211, 238, .14); border-color: rgba(34, 211, 238, .38); }
        [data-bs-theme="dark"] .btn-action-edit { color: #93c5fd; background: rgba(59, 130, 246, .16); border-color: rgba(59, 130, 246, .42); }
        [data-bs-theme="dark"] .btn-action-delete { color: #fca5a5; background: rgba(239, 68, 68, .16); border-color: rgba(239, 68, 68, .45); }
        [data-bs-theme="dark"] .btn-action-restore { color: #86efac; background: rgba(34, 197, 94, .16); border-color: rgba(34, 197, 94, .45); }
    </style>
    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-mini bg-body-tertiary" data-role-theme="{{ $roleTheme ?? 'default' }}">
<div class="app-wrapper">

    <!-- Navbar -->
    <nav class="app-header navbar navbar-expand text-bg-midnight" data-bs-theme="dark">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
                        <i class="ti ti-layout-sidebar-left-collapse" aria-hidden="true"></i>
                        <i class="ti ti-layout-sidebar-left-expand d-none" aria-hidden="true"></i>
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
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">
                        <img src="{{ auth()->user()->avatar }}" class="user-image rounded-circle shadow" alt="{{ auth()->user()->full_name }}">
                        <span class="d-none d-md-inline">{{ auth()->user()->full_name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                        <li class="user-header text-bg-secondary">
                            <img src="{{ auth()->user()->avatar }}" class="rounded-circle shadow" alt="{{ auth()->user()->full_name }}">
                            <p>
                                {{ auth()->user()->full_name }}
                                <small>{{ auth()->user()->roles_text }}</small>
                            </p>
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
                <span class="brand-text fw-bold fst-italic">
                    <span class="brand-word-hris">HRIS</span>&nbsp;<span class="brand-word-id">Indonesia</span>
                </span>
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
                @php
                    $ap  = $activeMenuPrefix;
                    $aEq = fn (?string $segment) => $ap === $segment;
                @endphp
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false" id="navigation">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-dashboard"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    @can('view_personal')
                    <li class="nav-item {{ request()->routeIs('my.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('my.*') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-user"></i>
                            <p>
                                Data Saya
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('my.profile') }}" class="nav-link {{ request()->routeIs('my.profile') ? 'active' : '' }}">
                                    <i class="ti ti-id-badge-2 nav-icon"></i>
                                    <p>Profil Saya</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('my.attendance') }}" class="nav-link {{ request()->routeIs('my.attendance') ? 'active' : '' }}">
                                    <i class="ti ti-clock-hour-4 nav-icon"></i>
                                    <p>Absensi Saya</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('my.leaves') }}" class="nav-link {{ request()->routeIs('my.leaves*') ? 'active' : '' }}">
                                    <i class="ti ti-plane nav-icon"></i>
                                    <p>Cuti & Izin</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('my.payrolls') }}" class="nav-link {{ request()->routeIs('my.payrolls') ? 'active' : '' }}">
                                    <i class="ti ti-wallet nav-icon"></i>
                                    <p>Slip Gaji Saya</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('view_master')
                    <li class="nav-item {{ $aEq('master') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $aEq('master') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-database"></i>
                            <p>
                                Master
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('master') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link {{ request()->routeIs('admin.'.$module['menu'].'.'.$module['key'].'.*') ? 'active' : '' }}">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endcan

                    @can('view_company')
                    <li class="nav-item {{ $aEq('company') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $aEq('company') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-building"></i>
                            <p>
                                Perusahaan
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('company') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link {{ request()->routeIs('admin.'.$module['menu'].'.'.$module['key'].'.*') ? 'active' : '' }}">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endcan

                    @can('view_employee')
                    <li class="nav-item {{ $aEq('employee') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $aEq('employee') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-users"></i>
                            <p>
                                Karyawan
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('employee') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link {{ request()->routeIs('admin.'.$module['menu'].'.'.$module['key'].'.*') ? 'active' : '' }}">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endcan

                    @can('view_address')
                    <li class="nav-item {{ $aEq('address') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $aEq('address') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-map-pin"></i>
                            <p>
                                Alamat
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('address') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link {{ request()->routeIs('admin.'.$module['menu'].'.'.$module['key'].'.*') ? 'active' : '' }}">
                                        <i class="ti ti-map-pin nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endcan

                    @can('view_attendance')
                    <li class="nav-item {{ $aEq('attendance') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $aEq('attendance') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-clock-hour-4"></i>
                            <p>
                                Absensi
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('attendance') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link {{ request()->routeIs('admin.'.$module['menu'].'.'.$module['key'].'.*') ? 'active' : '' }}">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                            <li class="nav-item">
                                <a href="{{ route('admin.attendance.attendances.upload') }}" class="nav-link {{ request()->routeIs('admin.attendance.attendances.upload') ? 'active' : '' }}">
                                    <i class="ti ti-upload nav-icon"></i>
                                    <p>Upload CSV</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.attendance.attendances.process') }}" class="nav-link {{ request()->routeIs('admin.attendance.attendances.process') ? 'active' : '' }}">
                                    <i class="ti ti-calendar-stats nav-icon"></i>
                                    <p>Proses Bulanan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.attendance.attendances.recap') }}" class="nav-link {{ request()->routeIs('admin.attendance.attendances.recap') ? 'active' : '' }}">
                                    <i class="ti ti-report-analytics nav-icon"></i>
                                    <p>Rekap</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('view_overtime')
                    <li class="nav-item {{ $aEq('overtime') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $aEq('overtime') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-clock-bolt"></i>
                            <p>
                                Lembur
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('overtime') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link {{ request()->routeIs('admin.'.$module['menu'].'.'.$module['key'].'.*') ? 'active' : '' }}">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                            <li class="nav-item">
                                <a href="{{ route('admin.overtime.overtimes.upload') }}" class="nav-link {{ request()->routeIs('admin.overtime.overtimes.upload') ? 'active' : '' }}">
                                    <i class="ti ti-upload nav-icon"></i>
                                    <p>Upload CSV</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.overtime.overtimes.process') }}" class="nav-link {{ request()->routeIs('admin.overtime.overtimes.process') ? 'active' : '' }}">
                                    <i class="ti ti-calendar-stats nav-icon"></i>
                                    <p>Proses Bulanan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.approvals.overtimes') }}" class="nav-link {{ request()->routeIs('admin.approvals.overtimes') ? 'active' : '' }}">
                                    <i class="ti ti-checklist nav-icon"></i>
                                    <p>Persetujuan</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('view_leave')
                    <li class="nav-item {{ $aEq('leave') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $aEq('leave') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-plane"></i>
                            <p>
                                Cuti
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('leave') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link {{ request()->routeIs('admin.'.$module['menu'].'.'.$module['key'].'.*') ? 'active' : '' }}">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                            <li class="nav-item">
                                <a href="{{ route('admin.approvals.leaves') }}" class="nav-link {{ request()->routeIs('admin.approvals.leaves') ? 'active' : '' }}">
                                    <i class="ti ti-checklist nav-icon"></i>
                                    <p>Persetujuan</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('view_payroll')
                    <li class="nav-item {{ $aEq('payroll') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $aEq('payroll') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-wallet"></i>
                            <p>
                                Penggajian
                                <i class="nav-arrow ti ti-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (\App\Support\MasterModules::byMenu('payroll') as $module)
                                <li class="nav-item">
                                    <a href="{{ route('admin.'.$module['menu'].'.'.$module['key'].'.index') }}" class="nav-link {{ request()->routeIs('admin.'.$module['menu'].'.'.$module['key'].'.*') ? 'active' : '' }}">
                                        <i class="ti ti-circle nav-icon"></i>
                                        <p>{{ $module['title'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                            <li class="nav-item">
                                <a href="{{ route('admin.payroll.payrolls.process') }}" class="nav-link {{ request()->routeIs('admin.payroll.payrolls.process') ? 'active' : '' }}">
                                    <i class="ti ti-calendar-stats nav-icon"></i>
                                    <p>Proses Payroll</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.payroll.payrolls.tax') }}" class="nav-link {{ request()->routeIs('admin.payroll.payrolls.tax') ? 'active' : '' }}">
                                    <i class="ti ti-report-money nav-icon"></i>
                                    <p>Proses Pajak</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.payroll.payrolls.recap') }}" class="nav-link {{ request()->routeIs('admin.payroll.payrolls.recap') ? 'active' : '' }}">
                                    <i class="ti ti-table-export nav-icon"></i>
                                    <p>Rekap Penggajian</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('view_user')
                    <li class="nav-item {{ request()->routeIs('admin.users*') ? 'menu-open' : '' }}">
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-user-shield"></i>
                            <p>Manajemen User</p>
                        </a>
                    </li>
                    @endcan

                    @can('view_config')
                    <li class="nav-item {{ request()->routeIs('admin.config.index') ? 'menu-open' : '' }}">
                        <a href="{{ route('admin.config.index') }}" class="nav-link {{ request()->routeIs('admin.config.index') ? 'active' : '' }}">
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

<!-- jQuery (dependency DataTables 2.x) -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<!-- Bootstrap 5.3 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/js/adminlte.min.js"></script>
<!-- DataTables 2.x -->
<script src="https://cdn.jsdelivr.net/npm/datatables.net@2.3.8/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.3.8/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('js/hris-master.js') }}"></script>
<script src="{{ asset('js/sidebar-toggle.js') }}"></script>
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
