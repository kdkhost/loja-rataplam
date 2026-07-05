@extends('master.front')

@section('title')
    Recuperar senha
@endsection

@section('style')
    @include('user.auth.partials.premium-style')
@endsection

@section('content')
    <section class="customer-auth-area" aria-label="Recuperação de senha">
        <div class="customer-auth-shell">
            <div class="customer-auth-showcase">
                <a class="customer-auth-logo" href="{{ route('front.index') }}" aria-label="{{ $setting->title }}">
                    <img src="{{ url('/core/public/storage/images/' . $setting->logo) }}" alt="{{ $setting->title }}">
                </a>

                <div class="customer-auth-copy">
                    <span class="customer-auth-kicker">Recuperar acesso</span>
                    <h1>Vamos ajudar você a entrar novamente.</h1>
                    <p>Informe o e-mail usado no cadastro e enviaremos um link seguro para redefinir sua senha.</p>
                </div>

                <div class="customer-auth-benefits is-stacked" aria-label="Segurança da recuperação">
                    <div class="customer-auth-benefit">
                        <i class="icon-shield"></i>
                        <strong>Link seguro</strong>
                        <span>A redefinição é enviada somente para o e-mail da conta.</span>
                    </div>
                    <div class="customer-auth-benefit">
                        <i class="icon-mail"></i>
                        <strong>Confira sua caixa de entrada</strong>
                        <span>Veja também a pasta de spam ou promoções.</span>
                    </div>
                </div>
            </div>

            <div class="customer-auth-card">
                <form class="customer-auth-form" method="POST" action="{{ route('user.forgot.submit') }}">
                    @csrf

                    <h2>Recuperar senha</h2>
                    <p class="customer-auth-subtitle">Digite seu e-mail para receber o link de redefinição.</p>

                    <div class="customer-auth-field">
                        <label for="email-for-pass">E-mail</label>
                        <div class="customer-auth-input">
                            <i class="icon-mail"></i>
                            <input class="form-control" type="email" name="email" id="email-for-pass" value="{{ old('email') }}" autocomplete="email" required>
                        </div>
                        @error('email')
                            <p class="customer-auth-error">{{ $message }}</p>
                        @enderror
                        <small class="customer-auth-note">Use o mesmo e-mail informado no cadastro.</small>
                    </div>

                    <div class="customer-auth-actions">
                        <a class="customer-auth-secondary" href="{{ route('user.login') }}">Voltar ao login</a>
                        <button class="customer-auth-submit" type="submit">
                            <span>Enviar link</span>
                            <i class="icon-send"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
