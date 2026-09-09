@props(['employee'])

<a href="{{ route('admin.users.edit', $employee) }}" class="btn btn-sm btn-outline-primary">
    <i class="ti ti-edit me-1"></i> Edit
</a>
