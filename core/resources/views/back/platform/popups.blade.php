@extends('master.back')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-0 bc-title"><b>Popups Promocionais</b></h3>
        </div>
    </div>

    @include('alerts.alerts')

    <form action="{{ route('back.platform.popups.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5><b>Popup promocional</b></h5>
                        <div class="form-group">
                            <label class="switch-primary">
                                <input type="checkbox" class="switch switch-bootstrap status" name="promo_popup_enabled" value="1" {{ $setting->promo_popup_enabled ? 'checked' : '' }}>
                                <span class="switch-body"></span>
                                <span class="switch-text">Ativar</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Tipo de popup</label>
                            <select name="promo_popup_mode" class="form-control">
                                <option value="manual" {{ $setting->promo_popup_mode == 'manual' ? 'selected' : '' }}>Promoção manual</option>
                                <option value="product" {{ $setting->promo_popup_mode == 'product' ? 'selected' : '' }}>Produto existente</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Produto da promoção</label>
                            <select name="promo_popup_item_id" class="form-control">
                                <option value="">Selecionar produto</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}" {{ (int) $setting->promo_popup_item_id === (int) $item->id ? 'selected' : '' }}>
                                        {{ $item->name }} - {{ \App\Helpers\PriceHelper::setPrice($item->discount_price) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Campanha</label>
                                    <select name="promo_popup_campaign_type" class="form-control">
                                        <option value="flash" {{ $setting->promo_popup_campaign_type == 'flash' ? 'selected' : '' }}>Promoção relâmpago</option>
                                        <option value="blackfriday" {{ $setting->promo_popup_campaign_type == 'blackfriday' ? 'selected' : '' }}>Black Friday</option>
                                        <option value="custom" {{ $setting->promo_popup_campaign_type == 'custom' ? 'selected' : '' }}>Personalizada</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Selo da campanha</label>
                                    <input type="text" name="promo_popup_badge" class="form-control" value="{{ $setting->promo_popup_badge }}" placeholder="OFERTA RELAMPAGO">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Título</label>
                            <input type="text" name="promo_popup_title" class="form-control" value="{{ $setting->promo_popup_title }}">
                        </div>
                        <div class="form-group">
                            <label>Texto</label>
                            <textarea name="promo_popup_text" class="form-control" rows="4">{{ $setting->promo_popup_text }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Texto do botão</label>
                            <input type="text" name="promo_popup_button_text" class="form-control" value="{{ $setting->promo_popup_button_text }}">
                        </div>
                        <div class="form-group">
                            <label>Link</label>
                            <input type="text" name="promo_popup_link" class="form-control" value="{{ $setting->promo_popup_link }}">
                        </div>
                        <div class="form-group">
                            <label>Atraso em segundos</label>
                            <input type="number" name="promo_popup_delay" class="form-control" min="0" value="{{ $setting->promo_popup_delay ?: 3 }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Início</label>
                                    <input type="datetime-local" name="promo_popup_starts_at" class="form-control" value="{{ $setting->promo_popup_starts_at ? \Carbon\Carbon::parse($setting->promo_popup_starts_at)->format('Y-m-d\TH:i') : '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fim</label>
                                    <input type="datetime-local" name="promo_popup_ends_at" class="form-control" value="{{ $setting->promo_popup_ends_at ? \Carbon\Carbon::parse($setting->promo_popup_ends_at)->format('Y-m-d\TH:i') : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Imagem</label>
                            <div class="mb-2">
                                <img class="admin-img md" src="{{ $setting->promo_popup_image ? url('/core/public/storage/images/'.$setting->promo_popup_image) : url('/core/public/storage/images/placeholder.png') }}" alt="Popup">
                            </div>
                            <label class="file">
                                <input type="file" accept="image/*" name="promo_popup_image" class="upload-photo">
                                <span class="file-custom text-left">{{ __('Upload Image...') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5><b>Popup de saída</b></h5>
                        <div class="form-group">
                            <label class="switch-primary">
                                <input type="checkbox" class="switch switch-bootstrap status" name="exit_popup_enabled" value="1" {{ $setting->exit_popup_enabled ? 'checked' : '' }}>
                                <span class="switch-body"></span>
                                <span class="switch-text">Ativar</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Título</label>
                            <input type="text" name="exit_popup_title" class="form-control" value="{{ $setting->exit_popup_title }}">
                        </div>
                        <div class="form-group">
                            <label>Texto</label>
                            <textarea name="exit_popup_text" class="form-control" rows="4">{{ $setting->exit_popup_text }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Cupom/desconto</label>
                            <input type="text" name="exit_popup_coupon" class="form-control" value="{{ $setting->exit_popup_coupon }}">
                        </div>
                        <div class="form-group">
                            <label>Texto do botão</label>
                            <input type="text" name="exit_popup_button_text" class="form-control" value="{{ $setting->exit_popup_button_text }}">
                        </div>
                        <div class="form-group">
                            <label>Link</label>
                            <input type="text" name="exit_popup_link" class="form-control" value="{{ $setting->exit_popup_link }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group text-center">
            <button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
        </div>
    </form>
</div>
@endsection
