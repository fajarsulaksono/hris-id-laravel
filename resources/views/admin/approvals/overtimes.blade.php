@extends('layouts.app')

@section('title', 'Persetujuan Lembur - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Persetujuan Lembur" subtitle="Tinjau dan proses permohonan lembur karyawan">
        <x-slot:actions>
            <a href="{{ route('admin.approvals.leaves') }}" class="btn btn-outline-secondary">
                <i class="ti ti-plane me-1"></i> Persetujuan Cuti
            </a>
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <x-card>
        <x-crud.flash />

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Karyawan</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th>Disetujui Oleh</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $overtime)
                        <tr>
                            <td>{{ $overtime->employee_name }}</td>
                            <td>{{ $overtime->overtime_date?->format(config('hris.format.date')) }}</td>
                            <td>
                                {{ $overtime->start_hour }} – {{ $overtime->end_hour }}
                                @if ($overtime->description && $overtime->description !== config('hris.overtime.invalid_message'))
                                    <div class="text-muted small">{{ $overtime->description }}</div>
                                @endif
                            </td>
                            <td>
                                @if ((int) $overtime->raw_value === 0)
                                    <span class="text-muted small">Hitung ulang</span>
                                @else
                                    {{ rtrim(rtrim(number_format((float) $overtime->raw_value, 2, ',', '.'), '0'), ',') }} jam
                                @endif
                            </td>
                            <td>
                                @if ($overtime->status?->value === 'approved')
                                    <span class="badge text-bg-success">Disetujui</span>
                                @elseif ($overtime->status?->value === 'rejected')
                                    <span class="badge text-bg-danger">Ditolak</span>
                                @else
                                    <span class="badge text-bg-warning">Menunggu</span>
                                @endif
                            </td>
                            <td>{{ $overtime->approved_by_name ?? '-' }}</td>
                            <td class="text-end">
                                @if ($overtime->status?->value === 'pending' && (int) $overtime->raw_value > 0)
                                    <form action="{{ route('admin.approvals.overtimes.approve', $overtime) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="ti ti-check me-1"></i> Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.approvals.overtimes.reject', $overtime) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-x me-1"></i> Tolak
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">Terkunci</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada permohonan lembur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $rows->links() }}
        </div>
    </x-card>
@endsection