@extends('layouts.app')

@section('title', 'Hari Libur - ' . config('app.name'))

@section('content-header')
    <x-crud.page-header title="Hari Libur" subtitle="Kelola daftar hari libur nasional / perusahaan">
        <x-slot:actions>
            @can('manage_master')
                <a href="{{ route('admin.master.holidays.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Tambah
                </a>
            @endcan
        </x-slot:actions>
    </x-crud.page-header>
@endsection

@section('content')
    <x-crud.flash />

    <x-card :header="'Daftar Hari Libur'">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($holidays as $holiday)
                    <tr>
                        <td>{{ $holiday->holiday_date->format('d-m-Y') }}</td>
                        <td>{{ $holiday->name }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.master.holidays.show', $holiday) }}" class="btn btn-outline-secondary" title="Lihat">
                                    <i class="ti ti-eye"></i>
                                </a>
                                @can('manage_master')
                                    <a href="{{ route('admin.master.holidays.edit', $holiday) }}" class="btn btn-outline-primary" title="Ubah">
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.master.holidays.destroy', $holiday) }}"
                                          onsubmit="return confirm('Hapus hari libur ini?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            <i class="ti ti-calendar-off"></i> Belum ada hari libur.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            {{ $holidays->links() }}
        </div>
    </x-card>
@endsection