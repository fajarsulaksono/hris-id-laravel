@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
        <i class="ti ti-alert-triangle me-1"></i>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif