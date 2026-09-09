@php
    $crumbs = $crumbs ?? \App\Support\Breadcrumbs::crumbs();
    $first = $crumbs[0] ?? null;
    if (! is_array($first) || ($first['label'] ?? '') !== 'Home') {
        array_unshift($crumbs, ['label' => 'Home', 'url' => route('dashboard')]);
    }
@endphp
<div class="row align-items-center mb-3">
    <div class="col-sm-6">
        <h3 class="mb-0">{{ $title }}</h3>
        @if (isset($subtitle))
            <div class="text-muted small mt-1">{{ $subtitle }}</div>
        @endif
    </div>
    <div class="col-sm-6">
        <div class="d-flex flex-wrap align-items-center justify-content-sm-end">
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
        </div>
    </div>
</div>

@if (isset($actions))
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap align-items-center justify-content-sm-end gap-2">
            {{ $actions }}
        </div>
    </div>
@endif