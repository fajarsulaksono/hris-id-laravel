@extends('layouts.app')

@section('title', 'Slip Gaji Saya - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Slip Gaji Saya" subtitle="Riwayat take home pay & unduhan slip"></x-crud.page-header>
@endsection

@section('content')
    <x-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th class="text-end">Take Home Pay</th>
                        <th class="text-end">Slip</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $payroll)
                        <tr>
                            <td>{{ $payroll->period_label ?? $payroll->period?->display ?? '-' }}</td>
                            <td class="text-end fw-semibold">
                                Rp {{ number_format((float) $payroll->take_home_pay, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('my.payrolls.pdf', $payroll) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="ti ti-download me-1"></i> PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Belum ada slip gaji untuk Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            {{ $rows->links('pagination::bootstrap-5') }}
        </div>
    </x-card>
@endsection
