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
    </style>
    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
    </div>

    <!-- Navbar -->
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-bs-toggle="dropdown" href="#">
                        <i class="ti ti-user-circle"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                        <span class="dropdown-header">{{ auth()->user()->full_name }}</span>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="ti ti-logout me-2"></i> Keluar
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Sidebar -->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}" class="brand-link">
                <img src="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/img/AdminLTELogo.png" alt="HRIS Logo" class="brand-image opacity-75 shadow">
                <span class="brand-text fw-light">HRIS Indonesia</span>
            </a>
        </div>

        <div class="sidebar-wrapper">
            <div class="pt-3 pb-3 d-flex">
                <div class="info">
                    <a href="#" class="d-block">{{ auth()->user()->full_name }}</a>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link">
                            <i class="nav-icon ti ti-dashboard"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    @can('view_employee')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-users"></i>
                            <p>
                                Karyawan
                                <i class="right ti ti-chevron-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="ti ti-circle nav-icon"></i>
                                    <p>Daftar Karyawan</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcan

                    @can('view_attendance')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-clock-hour-4"></i>
                            <p>Absensi</p>
                        </a>
                    </li>
                    @endcan

                    @can('view_payroll')
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-wallet"></i>
                            <p>Payroll</p>
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
@stack('scripts')
</body>
</html>