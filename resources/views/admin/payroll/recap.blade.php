@extends('layouts.app')

@section('title', 'Rekap Penggajian - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Rekap Penggajian" subtitle="Rekap take home pay, PPh21 & beban perusahaan per periode">
        <x-slot:actions>
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-back me-1"></i> Kembali
            </a>
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <form method="GET" action="{{ route('admin.payroll.payrolls.recap') }}" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label for="company_id" class="form-label mb-0 fw-medium">Perusahaan</label>
            <select class="form-select form-select-sm" id="company_id" name="company_id">
                <option value="">Semua Perusahaan</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->getKey() }}" @selected($company->getKey() === $companyId)>{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <label for="month" class="form-label mb-0 fw-medium">Bulan</label>
            <select class="form-select form-select-sm" id="month" name="month">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" @selected($i === $month)>Bulan {{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="col-auto">
            <label for="year" class="form-label mb-0 fw-medium">Tahun</label>
            <input type="number" class="form-control form-control-sm" id="year" name="year" min="2000" max="2099" value="{{ $year }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="ti ti-filter me-1"></i> Tampilkan
            </button>
        </div>
        @if ($rows->isNotEmpty())
            <div class="col-auto ms-auto">
                <a href="{{ route('admin.payroll.payrolls.recap.export', ['format' => 'xlsx'] + request()->query()) }}" class="btn btn-outline-success btn-sm">
                    <i class="ti ti-file-spreadsheet me-1"></i> Excel
                </a>
                <a href="{{ route('admin.payroll.payrolls.recap.export', ['format' => 'pdf'] + request()->query()) }}" class="btn btn-outline-danger btn-sm">
                    <i class="ti ti-file-type-pdf me-1"></i> PDF
                </a>
            </div>
        @endif
    </form>

    <div class="row g-3">
        <div class="col-12">
            <x-card header="Rekap Penggajian" :footer="$rows->isNotEmpty() ? null : null">
                @if ($rows->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Perusahaan</th>
                                <th>Periode</th>
                                <th class="text-end">Take Home Pay</th>
                                <th class="text-end">Pajak PPh21</th>
                                <th class="text-end">Beban Perusahaan</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td>{{ $row['no'] }}</td>
                                    <td>{{ $row['code'] }}</td>
                                    <td>{{ $row['name'] }}</td>
                                    <td>{{ $row['company'] }}</td>
                                    <td>{{ $row['period'] }}</td>
                                    <td class="text-end">{{ 'Rp.'.number_format($row['take_home_pay'], 0, ',', '.') }}</td>
                                    <td class="text-end">{{ 'Rp.'.number_format($row['tax_value'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ 'Rp.'.number_format($row['cost'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr class="fw-semibold">
                                <td colspan="5" class="text-end">Total</td>
                                <td class="text-end">{{ 'Rp.'.number_format($totals['take_home_pay'], 0, ',', '.') }}</td>
                                <td class="text-end">{{ 'Rp.'.number_format($totals['tax_value'], 0, ',', '.') }}</td>
                                <td class="text-end">{{ 'Rp.'.number_format($totals['cost'], 0, ',', '.') }}</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Belum ada payroll untuk periode ini. Jalankan Proses Payroll terlebih dahulu.</p>
                @endif
            </x-card>
        </div>
    </div>
@endsection
