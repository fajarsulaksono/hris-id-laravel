<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Slip Gaji {{ $payroll->employee?->full_name }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #212529; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #212529; padding-bottom: 8px; margin-bottom: 12px; }
        h1 { font-size: 15px; margin: 0; }
        .sub { font-size: 10px; color: #6c757d; margin-top: 2px; }
        h2 { font-size: 11px; border-bottom: 1px solid #adb5bd; padding-bottom: 3px; margin: 12px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 3px 6px; }
        .dashed th { border-bottom: 1px dashed #adb5bd; }
        .right { text-align: right; }
        .total td { font-weight: bold; border-top: 1px solid #212529; }
        .info { width: 100%; margin-bottom: 4px; }
        .info td { padding: 1px 6px; }
        .info .label { color: #6c757d; width: 30%; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Slip Gaji</h1>
            <div class="sub">{{ $payroll->period?->company?->name ?? 'Perusahaan' }}</div>
        </div>
        <div class="right">
            <div>Periode: {{ $payroll->period?->display ?? '-' }}</div>
            <div>{{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    <table class="info">
        <tr>
            <td class="label">Nama</td>
            <td>: {{ $payroll->employee?->full_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kode</td>
            <td>: {{ $payroll->employee?->code ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>: {{ $payroll->employee?->job_title_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Departemen</td>
            <td>: {{ $payroll->employee?->department_name ?? '-' }}</td>
        </tr>
    </table>

    <h2>Rincian Penghasilan &amp; Potongan</h2>
    <table class="dashed">
        @forelse ($details as $detail)
            @php $isMinus = $detail->component?->state === \App\Enums\SalaryState::MINUS; @endphp
            <tr>
                <td>{{ $detail->component?->name ?? 'Komponen' }}</td>
                <td class="right">{{ ($isMinus ? '-' : '') }} {{ number_format((float) $detail->benefit_value, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td>Tidak ada rincian.</td>
            </tr>
        @endforelse
        <tr class="total">
            <td>Take Home Pay</td>
            <td class="right">{{ number_format((float) $payroll->take_home_pay, 2, ',', '.') }}</td>
        </tr>
    </table>

    @if ($costs->isNotEmpty())
        <h2>Beban Perusahaan</h2>
        <table class="dashed">
            @foreach ($costs as $cost)
                <tr>
                    <td>{{ $cost->component?->name ?? 'Komponen' }}</td>
                    <td class="right">{{ number_format((float) $cost->benefit_value, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>
