@props(['fields', 'model'])

<dl class="row mb-0">
    @foreach ($fields as $field)
        @php
            $name = $field['name'];
            $type = $field['type'] ?? 'text';
            $value = $model->{$name};

            if ($type === 'checkbox') {
                $display = $value ? 'Ya' : 'Tidak';
            } elseif ($type === 'taglist') {
                $display = $value ? implode(', ', (array) $value) : '-';
            } elseif (isset($field['options'])) {
                $display = collect($field['options'])->firstWhere('value', $value)['label'] ?? ($value ?? '-');
            } elseif (isset($field['model'])) {
                $accessor = str_replace('_id', '_name', $name);
                $display = $model->{$accessor} ?? $value ?? '-';
            } elseif ($value instanceof \Carbon\CarbonInterface) {
                $display = $value->format('d/m/Y');
            } else {
                $display = $value ?? '-';
            }
        @endphp

        <dt class="col-sm-4 col-lg-3 text-muted fw-normal">{{ $field['label'] }}</dt>
        <dd class="col-sm-8 col-lg-9 fw-medium">{{ $display }}</dd>
    @endforeach
</dl>