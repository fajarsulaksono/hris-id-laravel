@extends('layouts.app')

@php
    $money = fn ($value) => 'Rp.'.number_format((float) $value, 2, ',', '.');
@endphp

@section('title', 'Detail Payroll - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Detail Payroll" subtitle="Slip gaji {{ $payroll->period?->display }}">
        <x-slot:actions>
            <a href="{{ route('admin.payroll.payrolls.pdf', $payroll->getKey()) }}" class="btn btn-outline-danger">
                <i class="ti ti-file-type-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-back me-1"></i> Kembali
            </a>
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-8">
            <x-card header="Rincian Gaji">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                    @forelse ($details as $detail)
                        @php $isMinus = $detail->component?->state === \App\Enums\SalaryState::MINUS; @endphp
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $detail->component?->name ?? 'Komponen' }}</span>
                                <div class="small text-muted">{{ $detail->component?->state_text ?? '' }}</div>
                            </td>
                            <td class="text-end {{ $isMinus ? 'text-danger' : 'text-success' }}">
                                {{ ($isMinus ? '-' : '+') }} {{ $money($detail->benefit_value) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-muted py-3">Belum ada rincian gaji untuk payroll ini.</td>
                        </tr>
                    @endforelse
                    <tr class="border-top">
                        <td class="fw-semibold">Take Home Pay</td>
                        <td class="text-end fw-semibold">{{ $money($payroll->take_home_pay) }}</td>
                    </tr>
                    </tbody>
                </table>
            </x-card>

            <x-card header="Beban Perusahaan (BPJS & JHT)" class="mt-3">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                    @forelse ($costs as $cost)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $cost->component?->name ?? 'Komponen' }}</span>
                                <div class="small text-muted">{{ $cost->component?->state_text ?? '' }}</div>
                            </td>
                            <td class="text-end">{{ $money($cost->benefit_value) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-muted py-3">Belum ada beban perusahaan untuk payroll ini.</td>
                        </tr>
                    @endforelse
                    @if ($costs->isNotEmpty())
                        <tr class="border-top">
                            <td class="fw-semibold">Total Beban Perusahaan</td>
                            <td class="text-end fw-semibold">{{ $money($costs->sum(fn ($c) => (float) $c->benefit_value)) }}</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </x-card>
        </div>

        <div class="col-lg-4">
            <x-card header="Karyawan">
                <div class="mb-2">
                    <div class="text-muted small">Nama</div>
                    <div class="fw-semibold">{{ $payroll->employee?->full_name ?? '-' }}</div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-muted small">Kode</div>
                        <div>{{ $payroll->employee?->code ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Perusahaan</div>
                        <div>{{ $payroll->employee?->company_name ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Departemen</div>
                        <div>{{ $payroll->employee?->department_name ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Jabatan</div>
                        <div>{{ $payroll->employee?->job_title_name ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Periode</div>
                        <div>{{ $payroll->period?->display ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Status</div>
                        <div>{{ $payroll->employee?->employee_status_text ?? '-' }}</div>
                    </div>
                </div>
            </x-card>

            @if ($tax)
                <x-card header="PPh21" class="mt-3">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Kelompok Pajak</span>
                        <span>{{ strtoupper((string) $tax->tax_group?->value) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">PTKP</span>
                        <span>{{ $money($tax->untaxable) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">PKP Tahunan</span>
                        <span>{{ $money($tax->taxable) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="fw-medium">Pajak Bulanan</span>
                        <span class="fw-semibold text-danger">{{ $money($tax->tax_value) }}</span>
                    </div>
                </x-card>
            @else
                <p class="text-muted small mt-3">Pajak untuk periode ini belum diproses.</p>
            @endif
        </div>
    </div>
@endsection