@extends('master.front')

@section('title')
    Verificar e-mail
@endsection

@section('style')
    @include('user.auth.partials.premium-style')
@endsection

@section('content')
    <section class="customer-auth-area" aria-label="Verificação de e-mail">
        <div class="customer-auth-shell">
            <div class="customer-auth-showcase">
                <a class="customer-auth-logo" href="{{ route('front.index') }}" aria-label="{{ $setting->title }}">
                    <img src="{{ url('/core/public/storage/images/' . $setting->logo) }}" alt="{{ $setting->title }}">
                </a>

                <div class="customer-auth-copy">
                    <span class="customer-auth-kicker">Verificação</span>
                    <h1>Confirme seu e-mail para ativar a conta.</h1>
                    <p>Digite o código recebido para liberar seu acesso com segurança.</p>
                </div>

                <div class="customer-auth-benefits is-stacked" aria-label="Confirmação de cadastro">
                    <div class="customer-auth-benefit">
                        <i class="icon-mail"></i>
                        <strong>Código por e-mail</strong>
                        <span>Confira a mensagem enviada para o endereço cadastrado.</span>
                    </div>
                    <div class="customer-auth-benefit">
                        <i class="icon-shield"></i>
                        <strong>Conta protegida</strong>
                        <span>Essa etapa ajuda a manter seus dados seguros.</span>
                    </div>
                </div>
            </div>

            <div class="customer-auth-card">
                <form class="customer-auth-form" method="post" action="{{ route('user.verify.submit') }}">
                    @csrf

                    <h2>Verificar e-mail</h2>
                    <p class="customer-auth-subtitle">Informe o código de verificação recebido no seu e-mail.</p>

                    <div class="customer-auth-field">
                        <label for="verify-code">Código de verificação</label>
                        <div class="customer-auth-input">
                            <i class="icon-check-circle"></i>
                            <input id="verify-code" class="form-control" type="text" name="verify" value="{{ old('verify') }}" inputmode="numeric" autocomplete="one-time-code" required>
                        </div>
                        @error('verify')
                            <p class="customer-auth-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="customer-auth-actions">
                        <a class="customer-auth-secondary" href="{{ route('user.login') }}">Voltar ao login</a>
                        <button class="customer-auth-submit" type="submit">
                            <span>Verificar</span>
                            <i class="icon-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
