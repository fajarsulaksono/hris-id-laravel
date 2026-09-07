<div @class(['card mb-3', 'shadow-sm' => ($shadow ?? false)])>
    @if (! empty($header))
        <div class="card-header d-flex align-items-center gap-2">
            <h5 class="card-title mb-0">{{ $header }}</h5>
            @if (! empty($headerTool))
                <div class="ms-auto">{{ $headerTool }}</div>
            @endif
        </div>
    @endif

    <div @class(['card-body', 'p-0' => ($flush ?? false)])>
        {{ $slot }}
    </div>

    @if (! empty($footer))
        <div class="card-footer bg-transparent">{{ $footer }}</div>
    @endif
</div>