@props(['fields', 'model'])

<dl class="row mb-0">
    @foreach ($fields as $field)
        @php
            $name = $field['name'];
            $type = $field['type'] ?? 'text';
            $value = $model->{$name};
            $isHtml = false;

            if ($type === 'image') {
                $collection = $field['collection'] ?? 'profile';
                $media = $model->getMedia($collection)->first();
                $display = $media
                    ? '<img src="'.$media->getUrl().'" alt="Foto" class="img-thumbnail" style="max-height:140px">'
                    : '-';
                $isHtml = (bool) $media;
            } elseif ($type === 'checkbox') {
                $display = $value ? 'Ya' : 'Tidak';
            } elseif ($type === 'taglist') {
                $display = $value ? implode(', ', (array) $value) : '-';
            } elseif (isset($field['options'])) {
                $display = collect($field['options'])->firstWhere('value', $value)['label'] ?? ($value ?? '-');
            } elseif (isset($field['model'])) {
                $rel = str_replace('_id', '', $name);
                $accessor = $rel.'_name';
                $display = $model->{$accessor} ?? $value ?? '-';
            } elseif ($value instanceof \Carbon\CarbonInterface) {
                $display = $value->format('d/m/Y');
            } else {
                $display = $value ?? '-';
            }
        @endphp

        <dt class="col-sm-4 col-lg-3 text-muted fw-normal">{{ $field['label'] }}</dt>
        <dd class="col-sm-8 col-lg-9 fw-medium">
            @if ($isHtml)
                {!! $display !!}
            @else
                {{ $display }}
            @endif
        </dd>
    @endforeach
</dl>