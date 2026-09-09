@extends('layouts.app')

@section('title', 'Dashboard')

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-primary">
                <div class="inner">
                    <h3>{{ $employeeCount ?? 0 }}</h3>
                    <p>Karyawan</p>
                </div>
                <div class="icon">
                    <i class="ti ti-users small-box-icon"></i>
                </div>
                <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    Detail <i class="ti ti-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-success">
                <div class="inner">
                    <h3>{{ $attendanceCount ?? 0 }}</h3>
                    <p>Absensi Hari Ini</p>
                </div>
                <div class="icon">
                    <i class="ti ti-clock-hour-4 small-box-icon"></i>
                </div>
                <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    Detail <i class="ti ti-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-warning">
                <div class="inner">
                    <h3>{{ $overtimeCount ?? 0 }}</h3>
                    <p>Overtime Bulan Ini</p>
                </div>
                <div class="icon">
                    <i class="ti ti-hourglass-high small-box-icon"></i>
                </div>
                <a href="#" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                    Detail <i class="ti ti-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box text-bg-danger">
                <div class="inner">
                    <h3>{{ $leaveCount ?? 0 }}</h3>
                    <p>Cuti Berlangsung</p>
                </div>
                <div class="icon">
                    <i class="ti ti-plane small-box-icon"></i>
                </div>
                <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                    Detail <i class="ti ti-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @if (count($departmentChart['values'] ?? []) > 0)
            <div class="col-lg-4 col-12">
                <x-card :header="'Karyawan per Departemen'">
                    <div id="chart-department"></div>
                </x-card>
            </div>
        @endif
        <div class="@if (count($departmentChart['values'] ?? []) > 0) col-lg-8 @else col-12 @endif col-12">
            <x-card :header="'Tren Kehadiran 12 Bulan'">
                <div id="chart-attendance"></div>
            </x-card>
        </div>
    </div>

    @if ($payrollChart)
        <div class="row">
            <div class="col-12">
                <x-card :header="'Tren Take Home Pay per Periode'">
                    <div id="chart-payroll"></div>
                </x-card>
            </div>
        </div>
    @endif

    @can('view_all')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Selamat datang, {{ auth()->user()->full_name }}!</h3>
                </div>
                <div class="card-body">
                    <p>Sistem HRIS Indonesia sedang dalam pengembangan.</p>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@4.7.0/dist/apexcharts.min.js"></script>
    <script>
        window.hrisDashboard = {
            department: @json($departmentChart),
            attendance: @json($attendanceChart),
            payroll: @json($payrollChart),
        };
    </script>
    <script>
        (function () {
            var data = window.hrisDashboard;
            if (!data || !window.ApexCharts) {
                return;
            }

            var charts = [];

            function isDark() {
                return document.documentElement.getAttribute('data-bs-theme') === 'dark';
            }

            function mode() {
                return { theme: { mode: isDark() ? 'dark' : 'light', palette: 'palette1' } };
            }

            function render() {
                charts.forEach(function (chart) {
                    try {
                        chart.destroy();
                    } catch (e) {}
                });
                charts = [];

                var el;
                var palette = ['#2563eb', '#0891b2', '#16a34a', '#f59e0b', '#dc2626', '#7c3aed', '#64748b', '#0ea5e9'];

                el = document.getElementById('chart-department');
                if (el && data.department && data.department.values && data.department.values.length) {
                    charts.push(new ApexCharts(el, Object.assign(mode(), {
                        chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
                        series: data.department.values,
                        labels: data.department.labels,
                        colors: palette,
                        legend: { position: 'bottom' },
                        dataLabels: { enabled: true, formatter: function (val) { return Math.round(val) + '%'; } },
                        tooltip: { y: { formatter: function (val) { return val + ' karyawan'; } } },
                    })));
                }

                el = document.getElementById('chart-attendance');
                if (el && data.attendance) {
                    charts.push(new ApexCharts(el, Object.assign(mode(), {
                        chart: { type: 'area', height: 300, fontFamily: 'inherit', toolbar: { show: false } },
                        series: [
                            { name: 'Hadir', data: data.attendance.present },
                            { name: 'Absen', data: data.attendance.absent },
                        ],
                        colors: ['#16a34a', '#dc2626'],
                        stroke: { curve: 'straight', width: 2 },
                        fill: { type: 'gradient', gradient: { opacityFrom: 0.45, opacityTo: 0.05 } },
                        dataLabels: { enabled: false },
                        legend: { position: 'top' },
                        xaxis: { categories: data.attendance.labels },
                        yaxis: { labels: { formatter: function (val) { return Math.round(val); } } },
                        tooltip: { shared: true },
                    })));
                }

                el = document.getElementById('chart-payroll');
                if (el && data.payroll && data.payroll.values && data.payroll.values.length) {
                    charts.push(new ApexCharts(el, Object.assign(mode(), {
                        chart: { type: 'bar', height: 320, fontFamily: 'inherit', toolbar: { show: false } },
                        series: [{ name: 'Total Gaji', data: data.payroll.values }],
                        colors: ['#2563eb'],
                        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                        dataLabels: { enabled: false },
                        legend: { position: 'top' },
                        xaxis: { categories: data.payroll.labels },
                        yaxis: {
                            labels: {
                                formatter: function (val) {
                                    if (val >= 1000000000) { return (val / 1000000000).toFixed(1) + ' M'; }
                                    if (val >= 1000000) { return (val / 1000000).toFixed(1) + ' jt'; }
                                    return Math.round(val);
                                },
                            },
                        },
                        tooltip: {
                            y: { formatter: function (val) { return 'Rp ' + Math.round(val).toLocaleString('id-ID'); } },
                        },
                    })));
                }

                charts.forEach(function (chart) { chart.render(); });
            }

            render();

            var toggle = document.getElementById('theme-toggle');
            if (toggle) {
                toggle.addEventListener('click', function () { setTimeout(render, 80); });
            }
        })();
    </script>
@endpush
