@extends('master.back')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-0 bc-title"><b>{{ __('PWA') }}</b></h3>
        </div>
    </div>

    @include('alerts.alerts')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('back.platform.pwa.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="switch-primary">
                        <input type="checkbox" class="switch switch-bootstrap status" name="is_pwa" value="1" {{ $setting->is_pwa ? 'checked' : '' }}>
                        <span class="switch-body"></span>
                        <span class="switch-text">{{ __('Enable') }} PWA</span>
                    </label>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nome do aplicativo</label>
                            <input type="text" name="pwa_name" class="form-control" value="{{ $setting->pwa_name ?: $setting->title }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nome curto</label>
                            <input type="text" name="pwa_short_name" class="form-control" value="{{ $setting->pwa_short_name ?: $setting->title }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Cor do tema</label>
                            <input type="color" name="pwa_theme_color" class="form-control" value="{{ $setting->pwa_theme_color ?: $setting->primary_color }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Cor de fundo</label>
                            <input type="color" name="pwa_background_color" class="form-control" value="{{ $setting->pwa_background_color ?: '#ffffff' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>URL inicial</label>
                            <input type="text" name="pwa_start_url" class="form-control" value="{{ $setting->pwa_start_url ?: '/' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Icone atual</label>
                            <div class="mb-2">
                                <img class="admin-img sm" src="{{ $setting->pwa_icon ? url('/core/public/storage/images/'.$setting->pwa_icon) : url('/core/public/storage/images/'.$setting->favicon) }}" alt="PWA">
                            </div>
                            <label class="file">
                                <input type="file" accept="image/*" name="pwa_icon" class="upload-photo">
                                <span class="file-custom text-left">{{ __('Upload Image...') }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info mb-0">
                            O manifesto e o service worker sao gerados dinamicamente pelo sistema. Quando o PWA estiver ativo, o site instala com aparencia de aplicativo Android.
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="switch-primary">
                                <input type="checkbox" class="switch switch-bootstrap status" name="pwa_install_popup_enabled" value="1" {{ $setting->pwa_install_popup_enabled ? 'checked' : '' }}>
                                <span class="switch-body"></span>
                                <span class="switch-text">Exibir popup de instalacao</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="switch-primary">
                                <input type="checkbox" class="switch switch-bootstrap status" name="pwa_auto_generate_icons" value="1" {{ $setting->pwa_auto_generate_icons ? 'checked' : '' }}>
                                <span class="switch-body"></span>
                                <span class="switch-text">Gerar icones automaticamente</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Atraso do popup em segundos</label>
                            <input type="number" min="0" max="120" name="pwa_install_popup_delay" class="form-control" value="{{ $setting->pwa_install_popup_delay ?: 3 }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Título do popup</label>
                            <input type="text" name="pwa_install_popup_title" class="form-control" value="{{ $setting->pwa_install_popup_title ?: 'Instale nosso aplicativo' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Texto do botão instalar</label>
                            <input type="text" name="pwa_install_popup_button_text" class="form-control" value="{{ $setting->pwa_install_popup_button_text ?: 'Instalar agora' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Texto do botão depois</label>
                            <input type="text" name="pwa_install_popup_later_text" class="form-control" value="{{ $setting->pwa_install_popup_later_text ?: 'Agora não' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Imagem do popup</label>
                            <div class="mb-2">
                                <img class="admin-img sm" src="{{ $setting->pwa_install_popup_image ? url('/core/public/storage/images/'.$setting->pwa_install_popup_image) : ($setting->pwa_icon ? url('/core/public/storage/images/'.$setting->pwa_icon) : url('/core/public/storage/images/'.$setting->favicon)) }}" alt="Popup PWA">
                            </div>
                            <label class="file">
                                <input type="file" accept="image/*" name="pwa_install_popup_image" class="upload-photo">
                                <span class="file-custom text-left">{{ __('Upload Image...') }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Texto de apresentação do popup</label>
                            <textarea name="pwa_install_popup_text" class="form-control" rows="4">{{ $setting->pwa_install_popup_text ?: 'Acesse mais rápido, receba uma experiência em tela cheia e use o site como aplicativo no seu celular.' }}</textarea>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Ícone 192x192</label>
                            <div class="mb-2">
                                <img class="admin-img sm" src="{{ $setting->pwa_icon_192 ? url('/core/public/storage/images/'.$setting->pwa_icon_192) : ($setting->pwa_icon ? url('/core/public/storage/images/'.$setting->pwa_icon) : url('/core/public/storage/images/'.$setting->favicon)) }}" alt="PWA 192">
                            </div>
                            <label class="file">
                                <input type="file" accept="image/png,image/jpeg,image/webp" name="pwa_icon_192" class="upload-photo">
                                <span class="file-custom text-left">Upload manual 192x192...</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Icone 512x512</label>
                            <div class="mb-2">
                                <img class="admin-img sm" src="{{ $setting->pwa_icon_512 ? url('/core/public/storage/images/'.$setting->pwa_icon_512) : ($setting->pwa_icon ? url('/core/public/storage/images/'.$setting->pwa_icon) : url('/core/public/storage/images/'.$setting->favicon)) }}" alt="PWA 512">
                            </div>
                            <label class="file">
                                <input type="file" accept="image/png,image/jpeg,image/webp" name="pwa_icon_512" class="upload-photo">
                                <span class="file-custom text-left">Upload manual 512x512...</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center">
                    <button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
