@php
    $crumbs = $crumbs ?? \App\Support\Breadcrumbs::crumbs();
    $first = $crumbs[0] ?? null;
    if (! is_array($first) || ($first['label'] ?? '') !== 'Home') {
        array_unshift($crumbs, ['label' => 'Home', 'url' => route('dashboard')]);
    }
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <nav class="mb-1 small" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                @foreach ($crumbs as $crumb)
                    @if (is_string($crumb))
                        <li class="breadcrumb-item active" aria-current="page">{{ $crumb }}</li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $crumb['url'] }}">
                                @if (($crumb['label'] ?? '') === 'Home')<i class="ti ti-home"></i> @endif{{ $crumb['label'] }}
                            </a>
                        </li>
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