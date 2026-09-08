<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #212529; }
        h1 { font-size: 14px; margin: 0 0 4px; }
        .meta { color: #6c757d; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dee2e6; padding: 4px 6px; text-align: left; }
        th { background: #f1f3f5; }
        .right { text-align: right; }
        tfoot td { font-weight: bold; background: #f8f9fa; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">Dibuat {{ now()->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Kode</th>
            <th>Nama</th>
            <th>Perusahaan</th>
            <th>Periode</th>
            <th class="right">Take Home Pay</th>
            <th class="right">Pajak PPh21</th>
            <th class="right">Beban Perusahaan</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['company'] }}</td>
                <td>{{ $row['period'] }}</td>
                <td class="right">{{ number_format($row['take_home_pay'], 0, ',', '.') }}</td>
                <td class="right">{{ number_format($row['tax_value'] ?? 0, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($row['cost'], 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Tidak ada data.</td>
            </tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr>
            <td colspan="5" class="right">Total</td>
            <td class="right">{{ number_format(collect($rows)->sum('take_home_pay'), 0, ',', '.') }}</td>
            <td class="right">{{ number_format(collect($rows)->sum('tax_value'), 0, ',', '.') }}</td>
            <td class="right">{{ number_format(collect($rows)->sum('cost'), 0, ',', '.') }}</td>
        </tr>
        </tfoot>
    </table>
</body>
</html>
