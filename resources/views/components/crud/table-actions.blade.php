@props(['module', 'row', 'trashed' => false])

@php
    $menu = $module['menu'];
    $base = 'admin.'.$menu.'.'.request()->route('moduleKey');
@endphp

<div class="btn-group btn-group-sm" role="group">
    @if (! $trashed)
        <a href="{{ route($base.'.show', $row) }}" class="btn btn-icon btn-outline-secondary" title="Lihat">
            <i class="ti ti-eye"></i>
        </a>

        @can('manage_'.$menu)
            <a href="{{ route($base.'.edit', $row) }}" class="btn btn-icon btn-outline-primary" title="Ubah">
                <i class="ti ti-pencil"></i>
            </a>
            <form method="POST" action="{{ route($base.'.destroy', $row) }}"
                  onsubmit="return confirm('Hapus {{ $module['title'] }} ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-icon btn-outline-danger" title="Hapus">
                    <i class="ti ti-trash"></i>
                </button>
            </form>
        @endcan
    @else
        <form method="POST" action="{{ route($base.'.restore', $row) }}">
            @csrf
            <button type="submit" class="btn btn-outline-success btn-icon" title="Pulihkan">
                <i class="ti ti-rotate-clockwise-2"></i>
            </button>
        </form>

        @can('manage_'.$menu)
            <form method="POST" action="{{ route($base.'.force-destroy', $row) }}"
                  onsubmit="return confirm('Hapus permanen {{ $module['title'] }} ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-icon" title="Hapus permanen">
                    <i class="ti ti-trash-x"></i>
                </button>
            </form>
        @endcan
    @endif
</div>