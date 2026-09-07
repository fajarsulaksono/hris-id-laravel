<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <nav class="mb-1 small" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Home</a></li>
                @foreach ($crumbs = ($crumbs ?? []) as $crumb)
                    @if (is_string($crumb))
                        <li class="breadcrumb-item active" aria-current="page">{{ $crumb }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">{{ $title }}</h4>
        @if (isset($subtitle))
            <div class="text-muted small">{{ $subtitle }}</div>
        @endif
    </div>
    @if (isset($actions))
        <div class="d-flex align-items-center gap-2">{{ $actions }}</div>
    @endif
</div>