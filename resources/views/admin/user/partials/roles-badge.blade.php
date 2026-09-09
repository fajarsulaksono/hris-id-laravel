@props(['roles'])

@php
    $colors = [
        'SUPER_ADMIN' => 'text-bg-danger',
        'TOP_LEVEL_MANAGEMENT' => 'text-bg-dark',
        'HRDIRECTOR' => 'text-bg-primary',
        'HRGENERAL_MANAGER' => 'text-bg-info',
        'HRMANAGER' => 'text-bg-warning',
        'HRSUPERVISOR' => 'text-bg-secondary',
        'HRSTAFF' => 'text-bg-secondary',
        'EMPLOYEE' => 'text-bg-light border',
    ];
@endphp

@foreach ($roles as $role)
    <span class="badge {{ $colors[strtoupper((string) $role)] ?? 'text-bg-light border' }} me-1">{{ $role }}</span>
@endforeach
