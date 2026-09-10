@extends('layouts.app')

@section('title', 'Persetujuan Cuti - '.config('app.name'))

@section('content-header')
    <x-crud.page-header title="Persetujuan Cuti" subtitle="Tinjau dan proses permohonan cuti/izin karyawan">
        <x-slot:actions>
            <a href="{{ route('admin.approvals.overtimes') }}" class="btn btn-outline-secondary">
                <i class="ti ti-clock-bolt me-1"></i> Persetujuan Lembur
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
                        <th>Tanggal Cuti</th>
                        <th>Alasan</th>
                        <th class="text-center">Hari</th>
                        <th>Status</th>
                        <th>Disetujui Oleh</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $leave)
                        <tr>
                            <td>{{ $leave->employee_name }}</td>
                            <td>{{ $leave->leave_date?->format(config('hris.format.date')) }}</td>
                            <td>
                                {{ $leave->reason_name }}
                                @if ($leave->description)
                                    <div class="text-muted small">{{ $leave->description }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $leave->amount }}</td>
                            <td>
                                @if ($leave->status?->value === 'approved')
                                    <span class="badge text-bg-success">Disetujui</span>
                                @elseif ($leave->status?->value === 'rejected')
                                    <span class="badge text-bg-danger">Ditolak</span>
                                @else
                                    <span class="badge text-bg-warning">Menunggu</span>
                                @endif
                            </td>
                            <td>{{ $leave->approved_by_name ?? '-' }}</td>
                            <td class="text-end">
                                @if ($leave->status?->value === 'pending')
                                    <form action="{{ route('admin.approvals.leaves.approve', $leave) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="ti ti-check me-1"></i> Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.approvals.leaves.reject', $leave) }}" method="POST" class="d-inline">
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
                            <td colspan="7" class="text-center text-muted py-4">Belum ada permohonan cuti.</td>
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