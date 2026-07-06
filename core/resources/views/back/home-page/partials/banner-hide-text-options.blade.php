@php
    $banner = $banner ?? [];
    $count = (int) ($count ?? 1);
    $prefix = $prefix ?? 'banner';
@endphp

<div class="form-group">
    <label class="d-block mb-2">Ocultar texto sobre a imagem</label>
    <div class="row">
        @for ($i = 1; $i <= $count; $i++)
            @php
                $field = 'hide_text' . $i;
                $id = $prefix . '_' . $field;
            @endphp
            <div class="col-md-4 col-sm-6 mb-2">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="{{ $field }}" id="{{ $id }}" value="1" {{ !empty($banner[$field]) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="{{ $id }}">Banner {{ $i }}</label>
                </div>
            </div>
        @endfor
    </div>
    <small class="form-text text-muted">Quando ativo, a imagem continua clicavel e o texto sobreposto fica oculto no site publico.</small>
</div>
