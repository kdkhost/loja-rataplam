@extends('master.back')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="mb-0 bc-title"><b>Correios Brasil</b></h3>
        </div>
    </div>

    @include('alerts.alerts')

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('back.platform.correios.update') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="switch-primary">
                        <input type="checkbox" class="switch switch-bootstrap status" name="correios_enabled" value="1" {{ $setting->correios_enabled ? 'checked' : '' }}>
                        <span class="switch-body"></span>
                        <span class="switch-text">Ativar Correios</span>
                    </label>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Modo</label>
                            <select name="correios_mode" class="form-control">
                                <option value="free" {{ $setting->correios_mode == 'free' ? 'selected' : '' }}>Gratuito/legado configuravel</option>
                                <option value="paid" {{ $setting->correios_mode == 'paid' ? 'selected' : '' }}>Oficial pago/contrato</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>CEP de origem</label>
                            <input type="text" name="correios_origin_cep" class="form-control cep-mask" value="{{ $setting->correios_origin_cep }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Servicos</label>
                            <input type="text" name="correios_services" class="form-control" value="{{ $setting->correios_services ?: '03220,03298' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Codigo empresa</label>
                            <input type="text" name="correios_company_code" class="form-control" value="{{ $setting->correios_company_code }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cartao de postagem</label>
                            <input type="text" name="correios_posting_card" class="form-control" value="{{ $setting->correios_posting_card }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Usuario Meu Correios</label>
                            <input type="text" name="correios_username" class="form-control" value="{{ $setting->correios_username }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Senha/chave</label>
                            <input type="password" name="correios_password" class="form-control" value="{{ $setting->correios_password }}">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Token API oficial</label>
                            <textarea name="correios_token" class="form-control" rows="3">{{ $setting->correios_token }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Dias adicionais</label>
                            <input type="number" min="0" name="correios_extra_days" class="form-control" value="{{ $setting->correios_extra_days ?: 0 }}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Endpoint gratuito/legado</label>
                            <input type="url" name="correios_free_endpoint" class="form-control" value="{{ $setting->correios_free_endpoint }}" placeholder="https://...">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5><b>Teste rápido</b></h5>
            <form action="{{ route('back.platform.correios.test') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-3"><input name="destination_cep" class="form-control cep-mask" placeholder="CEP destino" required></div>
                    <div class="col-md-2"><input name="weight" class="form-control" placeholder="Peso kg" value="1"></div>
                    <div class="col-md-2"><input name="height" class="form-control" placeholder="Altura" value="10"></div>
                    <div class="col-md-2"><input name="width" class="form-control" placeholder="Largura" value="20"></div>
                    <div class="col-md-2"><input name="length" class="form-control" placeholder="Comprimento" value="20"></div>
                    <div class="col-md-1"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button></div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
