@props(['field', 'model' => null])

@php
    $name = $field['name'];
    $type = $field['type'] ?? 'text';
    $current = $model ? $model->{$name} : null;
    $value = old($name, $current);
    $isInvalid = $errors->has($name);
@endphp

<div class="mb-3">
    <label for="{{ $name }}" class="form-label fw-medium">
        {{ $field['label'] }}
        @if (! empty($field['required'])) <span class="text-danger">*</span> @endif
    </label>

    @if ($type === 'textarea')
        <textarea class="form-control @if ($isInvalid) is-invalid @endif" id="{{ $name }}" name="{{ $name }}" rows="3">{{ $value }}</textarea>

    @elseif ($type === 'checkbox')
        <div class="form-check form-switch">
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox" class="form-check-input @if ($isInvalid) is-invalid @endif"
                   id="{{ $name }}" name="{{ $name }}" value="1"
                   @checked((bool) $value || (! $model && ($field['default'] ?? 0)) )>
            <label class="form-check-label" for="{{ $name }}">{{ $field['label'] }}</label>
        </div>

    @elseif ($type === 'image')
        @php
            $collection = $field['collection'] ?? 'profile';
            $media = $model?->getMedia($collection)->first();
        @endphp
        @if ($media)
            <div class="mb-2">
                <img src="{{ $media->getUrl() }}" alt="Preview" class="img-thumbnail" style="max-height: 140px">
            </div>
        @endif
        <input type="file" class="form-control @if ($isInvalid) is-invalid @endif"
               id="{{ $name }}" name="{{ $name }}" accept="image/*"
               @if (! empty($field['required'])) required @endif>
        @if ($media)
            <div class="form-text">Kosongkan untuk mempertahankan foto saat ini.</div>
        @endif

    @elseif ($type === 'select')
        @php
            $dependsType = match (($field['model'] ?? null)) {
                \App\Models\Master\City::class => 'city',
                \App\Models\Company\Department::class => 'department-by-company',
                \App\Models\Company\JobTitle::class => 'job-title-by-level',
                default => null,
            };
        @endphp
        <select class="form-select @if ($isInvalid) is-invalid @endif" id="{{ $name }}" name="{{ $name }}"
                @if (! empty($field['dependsOn']))
                    data-depends-on="{{ $field['dependsOn'] }}"
                    data-depends-url="{{ route('admin.api.options', ['type' => $dependsType]) }}"
                @endif>
            <option value="">---</option>

            @if (isset($field['options']))
                @foreach ($field['options'] as $option)
                    <option value="{{ $option['value'] }}" @selected($value == $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            @elseif (isset($field['model']) && empty($field['dependsOn']))
                @php
                    $modelClass = $field['model'];
                    $table = (new $modelClass)->getTable();
                    $sortColumn = \Illuminate\Support\Facades\Schema::hasColumn($table, 'name') ? 'name' :
                        (\Illuminate\Support\Facades\Schema::hasColumn($table, 'full_name') ? 'full_name' : null);
                    $options = collect($modelClass::orderBy($sortColumn ?? 'id')->get())
                        ->map(fn ($item) => ['id' => $item->getKey(), 'text' => $field['text'] === 'display' ? $item->display : $item->{$field['text']}])
                        ->sortBy('text');
                @endphp
                @foreach ($options as $option)
                    <option value="{{ $option['id'] }}" @selected($value == $option['id'])>
                        {{ $option['text'] }}
                    </option>
                @endforeach
            @endif
        </select>

    @elseif ($type === 'taglist')
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="ti ti-tags"></i></span>
            <input type="text" class="form-control @if ($isInvalid) is-invalid @endif"
                   id="{{ $name }}" name="{{ $name }}"
                   value="{{ old($name, $current ? implode(', ', $current) : '') }}" placeholder="mis. KPI, PAYROLL">
        </div>

    @else
        <div class="input-group">
            <input type="{{ $type === 'date' || $type === 'email' ? $type : 'text' }}"
                   class="form-control @if ($isInvalid) is-invalid @endif"
                   id="{{ $name }}"
                   name="{{ $name }}"
                   maxlength="{{ $field['max'] ?? 255 }}"
                   @if ($type === 'date' && $value instanceof \Carbon\CarbonInterface) value="{{ $value->toDateString() }}"
                   @else value="{{ $value }}" @endif
                   @if (! empty($field['required'])) required @endif>
        </div>
    @endif

    @error($name)
    <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>