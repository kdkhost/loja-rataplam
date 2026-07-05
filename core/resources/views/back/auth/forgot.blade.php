@extends('master.back-login')

@section('style')
    @include('back.auth.partials.premium-style')
@endsection

@section('content')
    <main class="admin-auth-page">
        <section class="admin-auth-shell" aria-label="Recuperação de senha administrativa">
            <div class="admin-auth-brand">
                <div>
                    <a class="admin-auth-logo-card" href="{{ route('front.index') }}" aria-label="{{ $setting->title }}">
                        <img src="{{ url('/core/public/storage/images/' . $setting->logo) }}" alt="{{ $setting->title }}">
                    </a>
                </div>

                <div class="admin-auth-brand-copy">
                    <span class="admin-auth-kicker">Acesso administrativo</span>
                    <h1>Recupere o acesso ao painel com segurança.</h1>
                    <p>O link de redefinição será enviado para o e-mail administrativo cadastrado.</p>
                </div>

                <div class="admin-auth-status" aria-label="Segurança do acesso">
                    <div class="admin-auth-status-item">
                        <strong>Conta protegida</strong>
                        <span>Processo restrito a administradores autorizados.</span>
                    </div>
                    <div class="admin-auth-status-item">
                        <strong>Link seguro</strong>
                        <span>Use o e-mail vinculado ao painel Rataplam.</span>
                    </div>
                </div>
            </div>

            <div class="admin-auth-panel">
                <div class="admin-auth-card">
                    <h2>Recuperar senha</h2>
                    <p class="auth-subtitle">Informe o e-mail do painel para receber o link de redefinição.</p>

                    <form action="{{ route('back.forgot.submit') }}" method="POST">
                        @csrf

                        @include('alerts.alerts')

                        <div class="admin-auth-field">
                            <label for="admin-forgot-email">E-mail</label>
                            <div class="admin-auth-input-wrap">
                                <i class="fas fa-envelope"></i>
                                <input id="admin-forgot-email" name="email" type="email" class="form-control" value="{{ old('email') }}" autocomplete="email" required>
                            </div>
                        </div>

                        <div class="admin-auth-actions">
                            <a href="{{ route('back.login') }}" class="admin-auth-secondary">Voltar ao login</a>
                            <button type="submit" class="admin-auth-submit">
                                <span class="fas fa-paper-plane"></span>
                                Enviar link
                            </button>
                        </div>

                        <p class="admin-auth-footnote">Verifique também a caixa de spam ou promoções.</p>
                    </form>
                </div>
            </div>
        </section>
    </main>
@endsection
