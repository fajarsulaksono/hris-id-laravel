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
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $employeeCount ?? 0 }}</h3>
                    <p>Karyawan</p>
                </div>
                <div class="icon">
                    <i class="ti ti-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $attendanceCount ?? 0 }}</h3>
                    <p>Absensi Hari Ini</p>
                </div>
                <div class="icon">
                    <i class="ti ti-clock-hour-4"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $overtimeCount ?? 0 }}</h3>
                    <p>Overtime Bulan Ini</p>
                </div>
                <div class="icon">
                    <i class="ti ti-hourglass-high"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $leaveCount ?? 0 }}</h3>
                    <p>Cuti Berlangsung</p>
                </div>
                <div class="icon">
                    <i class="ti ti-plane"></i>
                </div>
            </div>
        </div>
    </div>

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
