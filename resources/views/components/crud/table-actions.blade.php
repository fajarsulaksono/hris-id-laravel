@props(['module', 'row', 'trashed' => false])

@php
    $menu = $module['menu'];
    $base = 'admin.'.$menu.'.'.request()->route('moduleKey');
@endphp

<div class="row-actions" role="group">
    @if (! $trashed)
        <a href="{{ route($base.'.show', $row) }}" class="btn-action btn-action-view" title="Lihat">
            <i class="ti ti-eye"></i>
        </a>

        @if ($module['key'] === 'employees')
            <a href="{{ route($base.'.profile', $row) }}" class="btn-action btn-action-profile" title="Profil Karyawan">
                <i class="ti ti-user"></i>
            </a>
        @endif

        @can('manage_'.$menu)
            <a href="{{ route($base.'.edit', $row) }}" class="btn-action btn-action-edit" title="Ubah">
                <i class="ti ti-pencil"></i>
            </a>
            <form method="POST" action="{{ route($base.'.destroy', $row) }}"
                  onsubmit="return confirm('Hapus {{ $module['title'] }} ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-action-delete" title="Hapus">
                    <i class="ti ti-trash"></i>
                </button>
            </form>
        @endcan
    @else
        <form method="POST" action="{{ route($base.'.restore', $row) }}">
            @csrf
            <button type="submit" class="btn-action btn-action-restore" title="Pulihkan">
                <i class="ti ti-rotate-clockwise-2"></i>
            </button>
        </form>

        @can('manage_'.$menu)
            <form method="POST" action="{{ route($base.'.force-destroy', $row) }}"
                  onsubmit="return confirm('Hapus permanen {{ $module['title'] }} ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-action-delete" title="Hapus permanen">
                    <i class="ti ti-trash-x"></i>
                </button>
            </form>
        @endcan
    @endif
</div>